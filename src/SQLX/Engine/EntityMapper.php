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

namespace MNC\SQLX\Engine;

use Castor\Context;
use MNC\SQLX\Engine\Finder\ForEntities;
use MNC\SQLX\Engine\Mapper\FindClass;
use MNC\SQLX\Engine\Mapper\IdUpdater;
use MNC\SQLX\Engine\Mapper\LastId;
use MNC\SQLX\Engine\Mapper\RawRecord;
use MNC\SQLX\Engine\Metadata\Field;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Mapper\ConversionError;
use MNC\SQLX\SQL\Query\Comp;
use MNC\SQLX\SQL\Query\Delete;
use MNC\SQLX\SQL\Query\Insert;
use MNC\SQLX\SQL\Query\Select;
use MNC\SQLX\SQL\Query\Update;

/**
 * The EntityMapper is responsible for mapping an entity into.
 */
final class EntityMapper extends Mapper\Middleware
{
    public const CTX_QUERY = 'sqlx.query';
    public const QUERY_INSERT = 0;
    public const QUERY_UPDATE = 1;
    public const QUERY_DELETE = 2;

    private Metadata\Store $metadata;
    private PropertyAccessor\Store $accessor;

    public function __construct(Mapper $next, Metadata\Store $metadata, PropertyAccessor\Store $accessor)
    {
        parent::__construct($next);
        $this->metadata = $metadata;
        $this->accessor = $accessor;
    }

    /**
     * {@inheritDoc}
     */
    protected function tryToPHPValue(Context $ctx, Mapper $next, mixed $value): mixed
    {
        if ($value instanceof LastId) {
            return new IdUpdater($this->metadata, $this->accessor, $next, $value);
        }

        if ($value instanceof FindClass) {
            try {
                $metadata = $this->metadata->retrieve($value->classname);
            } catch (Metadata\Invalid|Metadata\NotFound $e) {
                throw new ConversionError('Metadata error', 0, $e);
            }

            return new ForEntities(
                $ctx,
                Select::all()->from($metadata->getTableName()),
                $value->connection,
                $metadata,
                $this,
                $value->tracker,
                $this->accessor,
            );
        }

        return $next->toPHPValue($ctx, $value);
    }

    /**
     * {@inheritDoc}
     */
    protected function tryToDatabaseValue(Context $ctx, Mapper $next, mixed $value): mixed
    {
        // If this is not an object, then we cannot map it
        if (!is_object($value)) {
            return $next->toDatabaseValue($ctx, $value);
        }

        $class = get_class($value);

        try {
            $metadata = $this->metadata->retrieve($class);
        } catch (Metadata\Invalid $e) {
            throw new Mapper\ConversionError('Invalid metadata', 0, $e);
        } catch (Metadata\NotFound) {
            // No metadata found means this is not an entity class.
            // We defer to the next mapper.
            return $next->toDatabaseValue($ctx, $value);
        }

        $operation = (int) ($ctx->value(self::CTX_QUERY) ?? -1);

        $accessor = $this->accessor->create($value);

        return match ($operation) {
            self::QUERY_INSERT => $this->buildInsert($ctx, $accessor, $metadata, $next),
            self::QUERY_UPDATE => $this->buildUpdate($ctx, $accessor, $metadata, $next),
            self::QUERY_DELETE => $this->buildDelete($ctx, $accessor, $metadata, $next),
            default => new RawRecord($metadata->getTableName()),
        };
    }

    /**
     * @param object $object
     *
     * @throws ConversionError
     */
    private function buildInsert(Context $ctx, PropertyAccessor $accessor, Metadata $metadata, Mapper $next): Insert
    {
        $insert = Insert::into($metadata->getTableName());

        $values = [];
        foreach ($metadata->getFields() as $field) {
            if ($field->isAutoincrement()) {
                continue;
            }

            $values[$field->column] = $this->mapFieldToDatabase($ctx, $accessor, $field, $next, $metadata->getClassName());
        }

        return $insert->values($values);
    }

    /**
     * @param object $object
     *
     * @throws ConversionError
     */
    private function buildUpdate(Context $ctx, PropertyAccessor $accessor, Metadata $metadata, Mapper $next): Update
    {
        $update = Update::table($metadata->getTableName());

        $set = [];
        foreach ($metadata->getFields() as $field) {
            $value = $this->mapFieldToDatabase($ctx, $accessor, $field, $next, $metadata->getClassName());

            if ($field->isId()) {
                $update->andWhere(Comp::eq($field->column, $value));

                continue;
            }

            $set[$field->column] = $value;
        }

        return $update->set($set);
    }

    /**
     * @param object $object
     *
     * @throws ConversionError
     */
    private function buildDelete(Context $ctx, PropertyAccessor $accessor, Metadata $metadata, Mapper $next): Delete
    {
        $delete = Delete::from($metadata->getTableName());

        foreach ($metadata->getFields() as $field) {
            if (!$field->isId()) {
                continue;
            }

            $value = $this->mapFieldToDatabase($ctx, $accessor, $field, $next, $metadata->getClassName());

            $delete->andWhere(Comp::eq($field->column, $value));
        }

        return $delete;
    }

    /**
     * @throws ConversionError
     */
    private function buildRawRecord(Context $ctx, PropertyAccessor $accessor, Metadata $metadata, Mapper $next): RawRecord
    {
        $record = new RawRecord($metadata->getTableName());

        foreach ($metadata->getFields() as $field) {
            $record->data[$field->column] = $this->mapFieldToDatabase($ctx, $accessor, $field, $next, $metadata->getClassName());
        }

        return $record;
    }

    /**
     * @throws ConversionError
     */
    private function mapFieldToDatabase(Context $ctx, PropertyAccessor $accessor, Field $field, Mapper $next, string $class): mixed
    {
        try {
            $value = $accessor->get($field->meta[Field::META_SCOPE] ?? $class, $field->name);

            return $next->toDatabaseValue($ctx, $value);
        } catch (Mapper\ConversionError|PropertyAccessor\NonexistentProperty $e) {
            throw new Mapper\ConversionError(sprintf(
                'Error while mapping property "%s" of class "%s"',
                $field->name,
                $class
            ), 0, $e);
        }
    }
}
