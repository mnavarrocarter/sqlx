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

namespace MNC\SQLX\Engine\Mapper;

use Castor\Context;
use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Metadata\Store;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\EngineError;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Mapper\ConversionError;

class IdUpdater
{
    private Metadata\Store $metadata;
    private PropertyAccessor\Store $accessor;
    private Mapper $mapper;
    private LastId $id;

    /**
     * @param Store $metadata
     */
    public function __construct(
        Metadata\Store $metadata,
        PropertyAccessor\Store $accessor,
        Mapper $mapper,
        LastId $id
    ) {
        $this->metadata = $metadata;
        $this->accessor = $accessor;
        $this->mapper = $mapper;
        $this->id = $id;
    }

    /**
     * @throws EngineError
     */
    public function update(Context $ctx, object $entity): void
    {
        $class = get_class($entity);

        try {
            $metadata = $this->metadata->retrieve($class);
        } catch (Metadata\Invalid|Metadata\NotFound $e) {
            throw new EngineError('Error while updating last inserted id', 0, $e);
        }

        $field = $this->getIdField($metadata);
        if (null === $field) {
            return;
        }

        try {
            $value = $this->mapper->toPHPValue(
                Context\withValue($ctx, Mapper::CTX_PHP_TYPE, $field->type),
                $this->id->getValue()
            );
        } catch (ConversionError $e) {
            throw new EngineError('Error while updating inserted id', 0, $e);
        }

        $accessor = $this->accessor->create($entity);

        try {
            $accessor->set($entity, $field->name, $value);
        } catch (PropertyAccessor\NonexistentProperty $e) {
            throw new EngineError('Error while updating inserted id', 0, $e);
        }
    }

    private function getIdField(Metadata $metadata): ?Metadata\Field
    {
        foreach ($metadata->getFields() as $field) {
            if ($field->isId() && $field->isAutoincrement()) {
                return $field;
            }
        }

        return null;
    }
}
