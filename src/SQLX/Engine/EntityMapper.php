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
use MNC\SQLX\Engine\Mapper\MapLastId;
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
final class EntityMapper implements Mapper
{
    public const CTX_QUERY = 'sqlx.query';
    public const QUERY_INSERT = 0;
    public const QUERY_UPDATE = 1;
    public const QUERY_DELETE = 2;

    private Mapper $next;
    private Metadata\Store $metadata;
    private PropertyAccessor\Store $accessor;

    public function __construct(Mapper $next, Metadata\Store $metadata, PropertyAccessor\Store $accessor)
    {
        $this->next = $next;
        $this->metadata = $metadata;
        $this->accessor = $accessor;
    }

    /**
     * {@inheritDoc}
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed
    {
        if ($value instanceof FindClass) {
            try {
                $metadata = $this->metadata->retrieve($value->classname);
            } catch (Metadata\Invalid|Metadata\NotFound $e) {
                throw new ConversionError('Metadata error', 0, $e);
            }

            $finder = new ForEntities(
                $ctx,
                Select::all()->from($metadata->getTableName()),
                $value->connection,
                $metadata,
                $this,
                $value->tracker,
                $this->accessor,
            );

            // Apply any hooks immediately
            Hooks\getFilters($ctx)->apply($finder, $metadata);

            return $finder;
        }

        if ($value instanceof MapLastId) {
            return $this->mapLastId($ctx, $value);
        }

        return $this->next->toPHPValue($ctx, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function toDatabaseValue(Context $ctx, mixed $value): mixed
    {
        // If this is not an object, then we cannot map it
        if (!is_object($value)) {
            return $this->next->toDatabaseValue($ctx, $value);
        }

        $class = get_class($value);

        try {
            $metadata = $this->metadata->retrieve($class);
        } catch (Metadata\Invalid $e) {
            throw new Mapper\ConversionError('Invalid metadata', 0, $e);
        } catch (Metadata\NotFound) {
            // No metadata found means this is not an entity class.
            // We defer to the next mapper.
            return $this->next->toDatabaseValue($ctx, $value);
        }

        $operation = (int) ($ctx->value(self::CTX_QUERY) ?? -1);

        $accessor = $this->accessor->create($value);

        $ctx = Mapper\withTableName($ctx, $metadata->getTableName());

        return match ($operation) {
            self::QUERY_INSERT => $this->buildInsert($ctx, $accessor, $metadata),
            self::QUERY_UPDATE => $this->buildUpdate($ctx, $accessor, $metadata),
            self::QUERY_DELETE => $this->buildDelete($ctx, $accessor, $metadata),
            default => new RawRecord($metadata->getTableName()),
        };
    }

    /**
     * @throws ConversionError
     */
    private function buildInsert(Context $ctx, PropertyAccessor $accessor, Metadata $metadata): Insert
    {
        $insert = Insert::into($metadata->getTableName());

        $values = [];
        foreach ($metadata->getFields() as $field) {
            if ($field->isAutoincrement()) {
                continue;
            }

            $values[$field->column] = $this->mapFieldToDatabase($ctx, $accessor, $field, $metadata->getClassName());
        }

        return $insert->values($values);
    }

    /**
     * @throws ConversionError
     */
    private function buildUpdate(Context $ctx, PropertyAccessor $accessor, Metadata $metadata): Update
    {
        $update = Update::table($metadata->getTableName());

        $set = [];
        foreach ($metadata->getFields() as $field) {
            $value = $this->mapFieldToDatabase($ctx, $accessor, $field, $metadata->getClassName());

            if ($field->isId()) {
                $update->andWhere(Comp::eq($field->column, $value));

                continue;
            }

            $set[$field->column] = $value;
        }

        return $update->set($set);
    }

    /**
     * @throws ConversionError
     */
    private function buildDelete(Context $ctx, PropertyAccessor $accessor, Metadata $metadata): Delete
    {
        $delete = Delete::from($metadata->getTableName());

        foreach ($metadata->getFields() as $field) {
            if (!$field->isId()) {
                continue;
            }

            $value = $this->mapFieldToDatabase($ctx, $accessor, $field, $metadata->getClassName());

            $delete->andWhere(Comp::eq($field->column, $value));
        }

        return $delete;
    }

    /**
     * @throws ConversionError
     */
    private function buildRawRecord(Context $ctx, PropertyAccessor $accessor, Metadata $metadata): RawRecord
    {
        $record = new RawRecord($metadata->getTableName());

        foreach ($metadata->getFields() as $field) {
            $record->data[$field->column] = $this->mapFieldToDatabase($ctx, $accessor, $field, $metadata->getClassName());
        }

        return $record;
    }

    /**
     * @throws ConversionError
     */
    private function mapFieldToDatabase(Context $ctx, PropertyAccessor $accessor, Field $field, string $class): mixed
    {
        $ctx = Mapper\withColumnName($ctx, $field->column);

        try {
            $value = $accessor->get($field->meta[Field::META_SCOPE] ?? $class, $field->name);

            return $this->next->toDatabaseValue($ctx, $value);
        } catch (Mapper\ConversionError|PropertyAccessor\NonexistentProperty $e) {
            throw new Mapper\ConversionError(sprintf(
                'Error while mapping property "%s" of class "%s"',
                $field->name,
                $class
            ), 0, $e);
        }
    }

    private function mapLastId(Context $ctx, MapLastId $cmd): mixed
    {
        $class = get_class($cmd->entity);

        $field = $this->getIdFieldFor($class);
        if (null === $field) {
            return null; // No id field means no mapping needed.
        }

        $rawId = $cmd->result->getLastInsertedId();
        if ('' === $rawId) {
            return ''; // This is unsupported to the driver.
        }

        $ctx = Mapper\withPHPType($ctx, $field->type);
        $ctx = Mapper\withMetadata($ctx, $field->meta);

        try {
            $value = $this->next->toPHPValue($ctx, $rawId);
        } catch (ConversionError $e) {
            // Unlikely to happen. We just return the raw value.
            return $rawId;
        }

        $accessor = $this->accessor->create($cmd->entity);

        try {
            $accessor->set($field->meta[Metadata\Field::META_SCOPE] ?? $class, $field->name, $value);
        } catch (PropertyAccessor\NonexistentProperty $e) {
            // Again, unlikely this happens. We just ignore.
        }

        return $value;
    }

    private function getIdFieldFor(string $class): ?Field
    {
        try {
            $metadata = $this->metadata->retrieve($class);
        } catch (Metadata\Invalid|Metadata\NotFound) {
            // This should never happen.
            return null;
        }

        foreach ($metadata->getFields() as $field) {
            if ($field->isId() && $field->isAutoincrement()) {
                return $field;
            }
        }

        return null;
    }
}
