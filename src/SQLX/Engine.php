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
use LogicException;
use MNC\SQLX\Engine\EntityMapper;
use MNC\SQLX\Engine\Mapper\IdUpdater;
use MNC\SQLX\Engine\Mapper\LastId;
use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Namer\Underscore;
use MNC\SQLX\Engine\Operator;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Mapper;
use Traversable;

class Engine
{
    public function __construct(
        private Operator $operator,
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

        $accessor = $config->getAccessorStore() ?? new PropertyAccessor\Store\Efficient();
        $metadata = $config->getMetadataStore() ?? new Metadata\Store\Reflection($namer);
        $operator = new Operator\Immediate($config->getConnection());

        return new self(
            $operator,
            $config->getTracker() ?? new Tracker\InMemory(),
            new EntityMapper($mapper, $metadata, $accessor)
        );
    }

    /**
     * Finds one object.
     */
    public function findOne(Context $ctx, string $class, mixed $where, mixed ...$args): object
    {
        throw new LogicException('Not Implemented');
    }

    public function findFirst(Context $ctx, string $class, mixed $where, mixed ...$args): object
    {
        throw new LogicException('Not Implemented');
    }

    /**
     * Finds many objects.
     */
    public function findMany(Context $ctx, string $class, mixed $where, mixed ...$args): Traversable
    {
        throw new LogicException('Not Implemented');
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
            $cmd = $this->toCommand($ctx, $entity, EntityMapper::CMD_UPDATE);
        } else {
            $cmd = $this->toCommand($ctx, $entity, EntityMapper::CMD_INSERT);
        }

        try {
            $result = $this->operator->execute($ctx, $cmd);
        } catch (Operator\ExecutionError $e) {
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
     */
    public function delete(Context $ctx, object $entity): void
    {
        throw new LogicException('Not Implemented');
    }

    /**
     * @throws EngineError
     */
    private function toCommand(Context $ctx, object $entity, int $operation): object
    {
        $class = get_class($entity);

        $ctx = Context\withValue($ctx, EntityMapper::CTX_CMD, $operation);

        try {
            $mapped = $this->mapper->toDatabaseValue($ctx, $entity);
        } catch (Mapper\ConversionError $e) {
            throw new EngineError(sprintf('Error while mapping %s', $class), 0, $e);
        }

        if (!is_object($mapped)) {
            throw new EngineError(sprintf('Mapped value from %s is not an object', $class));
        }

        return $mapped;
    }

    private function toObject(Context $ctx, array $row): object
    {
        throw new LogicException('Not implemented');
    }
}
