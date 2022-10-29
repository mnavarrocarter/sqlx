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
use LogicException;
use MNC\SQLX\Engine\Mapper\Classname;
use MNC\SQLX\Engine\Mapper\IdUpdater;
use MNC\SQLX\Engine\Mapper\LastId;
use MNC\SQLX\Engine\Metadata\Field;
use MNC\SQLX\Engine\Operator\Cmd;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Mapper\ConversionError;

final class EntityMapper extends Mapper\Middleware
{
    public const CTX_CMD = 'sqlx.cmd';
    public const CMD_INSERT = 0;
    public const CMD_UPDATE = 1;
    public const CMD_DELETE = 2;

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

        if (!$value instanceof Classname) {
            return $next->toPHPValue($ctx, $value);
        }

        throw new LogicException('Not Implemented');
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

        return $this->toCommand($ctx, $metadata, $next, $value);
    }

    /**
     * @throws ConversionError
     */
    private function toCommand(Context $ctx, Metadata $metadata, Mapper $next, object $object): object
    {
        $operation = (int) ($ctx->value(self::CTX_CMD) ?? -1);

        $cmd = match ($operation) {
            self::CMD_INSERT => new Cmd\Insert(),
            self::CMD_UPDATE => new Cmd\Update(),
            self::CMD_DELETE => new Cmd\Delete(),
            default => new Cmd\Record(),
        };

        $cmd->table = $metadata->getTableName();

        $accessor = $this->accessor->create($object);

        foreach ($metadata->getFields() as $field) {
            // We don't process non-id fields for deletes
            if ($cmd instanceof Cmd\Delete && !$field->isId()) {
                continue;
            }

            // We don't process autoincrement fields for inserts
            if ($cmd instanceof Cmd\Insert && $field->isAutoincrement()) {
                continue;
            }

            try {
                $value = $accessor->get($field->meta[Field::META_SCOPE] ?? get_class($object), $field->name);
                $value = $next->toDatabaseValue($ctx, $value);
            } catch (Mapper\ConversionError|PropertyAccessor\NonexistentProperty $e) {
                throw new Mapper\ConversionError(sprintf(
                    'Error while mapping property "%s" of class "%s"',
                    $field->name,
                    $metadata->getClassName()
                ), 0, $e);
            }

            if ($cmd instanceof Cmd\Record) {
                $cmd->values[$field->column] = $value;

                continue;
            }

            if ($cmd instanceof Cmd\Insert) {
                $cmd->values[$field->column] = $value;

                continue;
            }

            if ($cmd instanceof Cmd\Delete) {
                $cmd->where[$field->column] = $value;

                continue;
            }

            if ($field->isId()) {
                $cmd->where[$field->column] = $value;
            }

            $cmd->values[$field->column] = $value;
        }

        return $cmd;
    }
}
