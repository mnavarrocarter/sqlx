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

namespace MNC\SQLX\Engine\Metadata\Store;

use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Metadata\Invalid;
use MNC\SQLX\Engine\Metadata\NotFound;
use MNC\SQLX\Engine\Metadata\Store;
use MNC\SQLX\Engine\Namer;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

final class Reflection implements Store
{
    private Namer $namer;

    /**
     * @var array<string, Metadata>
     */
    private array $cache = [];

    /**
     * @var string[]
     */
    private array $notFound = [];

    public function __construct(Namer $namer)
    {
        $this->namer = $namer;
    }

    public function retrieve(string $class): Metadata
    {
        if (in_array($class, $this->notFound, true)) {
            throw new NotFound(sprintf('Metadata not found for class %s', $class));
        }

        $metadata = $this->cache[$class] ?? null;

        if (!$metadata instanceof Metadata) {
            try {
                $metadata = $this->build($class);
            } catch (NotFound $e) {
                // We remember the not found errors to avoid reflecting too often
                $this->notFound[] = $class;

                throw $e;
            }
            $this->cache[$class] = $metadata;
        }

        return $metadata;
    }

    /**
     * @throws Invalid
     * @throws NotFound
     */
    private function build(string $class): Metadata
    {
        try {
            $rClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Invalid('Reflection error', 0, $e);
        }

        // First, we check if the class has an entity attribute
        $attrs = $rClass->getAttributes(Metadata\Entity::class);
        if ([] === $attrs) {
            throw new NotFound(sprintf('No entity attribute for class %s', $class));
        }

        /** @var Metadata\Entity $entity */
        $entity = $attrs[0]->newInstance();

        if ('' === $entity->table) {
            $entity->table = $this->namer->classToTable($class);
        }

        $metadata = new Metadata\InMemory($class, $entity->table, [$rClass, 'newInstanceWithoutConstructor']);

        foreach ($rClass->getProperties() as $prop) {
            $this->buildAndInsert($metadata, $prop);
        }

        $parent = get_parent_class($class);

        while ($parent) {
            try {
                $rClass = new ReflectionClass($parent);
            } catch (ReflectionException $e) {
                throw new Invalid(sprintf('Error while reflecting superclass of %s', $class), 0, $e);
            }

            foreach ($rClass->getProperties() as $prop) {
                $this->buildAndInsert($metadata, $prop);
            }

            $parent = get_parent_class($parent);
        }

        return $metadata;
    }

    private function buildAndInsert(Metadata\InMemory $metadata, ReflectionProperty $prop): void
    {
        // Ignored attribute
        $ignore = [] !== $prop->getAttributes(Metadata\Ignore::class);
        if ($ignore) {
            return;
        }

        // We don't map static properties
        if ($prop->isStatic()) {
            return;
        }

        $class = $prop->getDeclaringClass()->getName();

        $field = ($prop->getAttributes(Metadata\Field::class)[0] ?? null)?->newInstance() ?? new Metadata\Field();

        // Complete the field with reflection metadata
        if ('' === $field->name) {
            $field->name = $prop->getName();
        }

        if ('' === $field->type) {
            [$type, $nullable] = $this->getType($prop);
            $field->type = $type;
            $field->nullable = $nullable;
        }

        if ('' === $field->column) {
            $field->column = $this->namer->propertyToColumn($class, $field->name);
        }

        if (null === $field->default && $prop->hasDefaultValue()) {
            $field->default = $prop->getDefaultValue();
        }

        // We put the scope
        $field->meta[Metadata\Field::META_SCOPE] = $class;

        // Is this field part of an id?
        $id = ($prop->getAttributes(Metadata\Id::class)[0] ?? null)?->newInstance();

        if (!$id instanceof Metadata\Id) {
            $metadata->addField($field);

            return;
        }

        $flags = Metadata\Field::FLAG_ID;

        // If id is int, most likely is autoincrement
        if (null === $id->autoincrement) {
            $id->autoincrement = 'integer' === $field->type || 'int' === $field->type;
        }

        if ($id->autoincrement) {
            $flags = Metadata\Field::FLAG_ID | Metadata\Field::FLAG_AUTOINCREMENT;
        }

        $field->meta[Metadata\Field::META_FLAGS] = $flags;

        $metadata->addField($field);
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function getType(ReflectionProperty $prop): array
    {
        $type = $prop->getType();

        if ($type instanceof ReflectionNamedType) {
            return [$type->getName(), $type->allowsNull()];
        }

        if ($type instanceof ReflectionUnionType) {
            return ['', $type->allowsNull()];
        }

        return ['', false];
    }
}
