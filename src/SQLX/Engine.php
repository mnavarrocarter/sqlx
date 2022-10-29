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
use MNC\SQLX\Engine\Mapper\IdUpdater;
use MNC\SQLX\Engine\Mapper\LastId;
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
        private EntityMapper $mapper,
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
        $query = new FindClass($this->connection, $this->tracker, $class);

        try {
            $finder = $this->mapper->toPHPValue($ctx, $query);
        } catch (Mapper\ConversionError $e) {
            throw new EngineError(sprintf('Error while mapping finder for class %s', $class), 0, $e);
        }

        if (!$finder instanceof Finder) {
            throw new EngineError(sprintf('Returned mapped value is not an instance of %s', Finder::class));
        }

        return $finder;
    }

    /**
     * Persists an object into the database.
     *
     * If the object has been tracked by the engine, it performs an update,
     * otherwise it inserts it.
     *
     * @throws EngineError if there is an error in the operation
     */
    public function persist(Context $ctx, object $entity): void
    {
        $isTracked = $this->tracker->isTracked($entity);

        if ($isTracked) {
            $query = $this->toStatement($ctx, $entity, EntityMapper::QUERY_UPDATE);
        } else {
            $query = $this->toStatement($ctx, $entity, EntityMapper::QUERY_INSERT);
        }

        try {
            $result = $this->connection->execute($ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new EngineError('Error while persisting', 0, $e);
        }

        if (!$isTracked) {
            $this->tracker->track($entity);

            $lastId = new LastId($result);

            try {
                $updater = $this->mapper->toPHPValue($ctx, $lastId);
            } catch (Mapper\ConversionError $e) {
                throw new EngineError('Error while trying to update the id', 0, $e);
            }

            if ($updater instanceof IdUpdater) {
                $updater->update($ctx, $entity);
            }
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
            throw new EngineError('Cannot delete an untracked object.');
        }

        $query = $this->toStatement($ctx, $entity, EntityMapper::QUERY_DELETE);

        try {
            $this->connection->execute($ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new EngineError('Error while deleting', 0, $e);
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
            throw new EngineError(sprintf('Error while mapping %s', $class), 0, $e);
        }

        if (!$mapped instanceof Statement) {
            throw new EngineError(sprintf('Mapped value from %s is not an statement', $class));
        }

        return $mapped;
    }
}
