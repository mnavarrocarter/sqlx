<?php

declare(strict_types=1);

/**
 * @project MNC SQLX
 * @link https://github.com/mnavarrocarter/sqlx
 * @project mnavarrocarter/sqlx
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license BSD-3-Clause
 * @copyright 2022 Castor Labs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MNC\SQLX;

use Castor\Context;
use MNC\SQLX\Engine\EntityMapper;
use MNC\SQLX\Engine\Finder;
use MNC\SQLX\Engine\Mapper\FindClass;
use MNC\SQLX\Engine\Mapper\MapLastId;
use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Namer\Underscore;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Statement;

class Engine
{
    public function __construct(
        private Connection $connection,
        private Tracker $tracker,
        private Mapper $mapper,
    ) {
    }

    public static function configure(Connection $conn): Configuration
    {
        return new Configuration($conn);
    }

    public static function make(Configuration $config): Engine
    {
        $mapper = new Mapper\Standard();
        foreach ($config->getMappers() as $mapperFn) {
            $mapper = $mapperFn($mapper);
        }

        $namer = $config->getNamer() ?? new Underscore();

        $accessor = $config->getAccessorStore() ?? new PropertyAccessor\Store\ClosureBased();
        $metadata = $config->getMetadataStore() ?? new Metadata\Store\Reflection($namer);

        return new self(
            $config->getConnection(),
            $config->getTracker() ?? new Tracker\InMemory(),
            new EntityMapper($mapper, $metadata, $accessor)
        );
    }

    /**
     * Finds one object.
     *
     * @throws EngineError
     */
    public function find(Context $ctx, string $class): Finder
    {
        $cmd = new FindClass($this->connection, $this->tracker, $class);

        try {
            $finder = $this->mapper->toPHPValue($ctx, $cmd);
        } catch (Mapper\ConversionError $e) {
            throw new EngineError(sprintf(
                'Error while creating finder for class %s. Have you registered it as an entity?',
                $class,
            ), 0, $e);
        }

        if (!$finder instanceof Finder) {
            throw new EngineError(sprintf(
                'Returned mapped value is not an instance of %s. Check your configuration as %s should be the first mapper in your chain.',
                Finder::class,
                EntityMapper::class
            ));
        }

        return $finder;
    }

    /**
     * Persists an object into the database.
     *
     * If the object has been tracked by the engine, it performs an update,
     * otherwise it inserts it.
     *
     * For an insert operation and only for autoincrement ids, the engine will
     * try to fetch the last inserted id from the database and add it to your
     * object. Depending on the driver, this is not always possible and your
     * object MAY not have the id. Therefore, use this feature judiciously.
     *
     * If you really need to know the ids before insertion, use a form of
     * deterministic IDs like UUIDS.
     *
     * @throws EngineError if there is an error in the operation
     */
    public function persist(Context $ctx, object $entity): void
    {
        $isTracked = $this->tracker->isTracked($entity);

        $operation = $isTracked ? EntityMapper::QUERY_UPDATE : EntityMapper::QUERY_INSERT;

        $query = $this->toStatement($ctx, $entity, $operation);

        try {
            $result = $this->connection->execute($ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new EngineError('An error occurred while executing the query. Check previous errors for details.', 0, $e);
        }

        if ($isTracked) {
            return;
        }

        $this->tracker->track($entity);

        $cmd = new MapLastId($result, $entity);

        try {
            $this->mapper->toPHPValue($ctx, $cmd);
        } catch (Mapper\ConversionError $e) {
            throw new EngineError('Unexpected error trying to map the last inserted id. Check previous errors for details.', 0, $e);
        }
    }

    /**
     * Deletes an object from the database.
     *
     * The object must be "tracked" by the engine, otherwise deletion will fail.
     *
     * @throws EngineError
     */
    public function delete(Context $ctx, object $entity): void
    {
        if (!$this->tracker->isTracked($entity)) {
            throw new EngineError('Cannot delete an untracked object. Please retrieve it first using the finder.');
        }

        $query = $this->toStatement($ctx, $entity, EntityMapper::QUERY_DELETE);

        try {
            $this->connection->execute($ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new EngineError('An error occurred while executing the query. Check previous errors for details.', 0, $e);
        }

        $this->tracker->forget($entity);
    }

    /**
     * @throws EngineError
     */
    private function toStatement(Context $ctx, object $entity, int $operation): Statement
    {
        $class = get_class($entity);

        $ctx = Context\withValue($ctx, EntityMapper::CTX_QUERY, $operation);

        try {
            $mapped = $this->mapper->toDatabaseValue($ctx, $entity);
        } catch (Mapper\ConversionError $e) {
            throw new EngineError(sprintf(
                'Error while trying to build SQL statement for class %s. Check the previous error for more details.',
                $class
            ), 0, $e);
        }

        if (!$mapped instanceof Statement) {
            throw new EngineError(sprintf(
                'Returned mapped value is not an instance of %s. Check your configuration as %s should be the first mapper in your chain.',
                Statement::class,
                EntityMapper::class
            ));
        }

        return $mapped;
    }
}
