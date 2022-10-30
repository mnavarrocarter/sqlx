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

namespace MNC\SQLX\Engine\Finder;

use Castor\Context;
use Generator;
use IteratorAggregate;
use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Metadata\Field;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\PropertyAccessor\Store;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection\Rows;
use MNC\SQLX\SQL\Connection\ScanError;
use MNC\SQLX\SQL\Mapper;

final class HydratableRows implements Rows, IteratorAggregate
{
    public function __construct(
        private Context $ctx,
        private Rows $rows,
        private Metadata $metadata,
        private Mapper $mapper,
        private Tracker $tracker,
        private Store $accessor
    ) {
    }

    /**
     * @throws ScanError
     */
    public function scanAssoc(array &$value): void
    {
        $data = [];
        $this->rows->scanAssoc($data);

        if ([] === $data) {
            $value = [];

            return;
        }

        $class = $this->metadata->getClassName();

        $value = $this->mapFieldsToArray($class, $data);
    }

    /**
     * Scans an object.
     *
     * @param ...$values
     */
    public function scan(&...$values): void
    {
        array_walk($values, [$this, 'doScan']);
    }

    /**
     * Dumps all the objects in memory.
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @throws ScanError
     */
    public function getIterator(): Generator
    {
        $isArray = HYDRATION_ARRAY === getHydrationMode($this->ctx);

        while (true) {
            if ($isArray) {
                $arr = [];
                $this->scanAssoc($arr);
                if ([] === $arr) {
                    break;
                }

                yield $arr;

                continue;
            }

            $object = null;
            $this->doScan($object);
            if (!is_object($object)) {
                break;
            }

            yield $object;
        }
    }

    /**
     * @throws ScanError
     */
    private function doScan(mixed &$value): void
    {
        $data = [];
        $this->rows->scanAssoc($data);

        if ([] === $data) {
            $value = null;

            return;
        }

        $class = $this->metadata->getClassName();

        $data = $this->mapFieldsToArray($class, $data);

        $object = $this->metadata->newInstance();

        $accessor = $this->accessor->create($object);

        foreach ($data as $prop => $val) {
            $field = $this->metadata->getFieldByProp($prop);
            if (null === $field) {
                throw new ScanError(sprintf('No field found in class %s for prop name %s', $class, $prop));
            }

            try {
                $accessor->set($field->meta[Field::META_SCOPE] ?? $class, $prop, $val);
            } catch (PropertyAccessor\NonexistentProperty $e) {
                throw new ScanError(sprintf('Error while setting property %s on class %s', $prop, $class), 0, $e);
            }
        }

        $this->tracker->track($object);
        $value = $object;
    }

    /**
     * @throws ScanError
     */
    private function mapFieldsToArray(string $class, array $values): array
    {
        $data = [];

        foreach ($values as $column => $value) {
            $field = $this->metadata->getFieldByColumn($column);
            if (null === $field) {
                throw new ScanError(sprintf('No field found in class %s for column name %s', $class, $column));
            }

            try {
                $ctx = Context\withValue($this->ctx, Mapper::CTX_PHP_TYPE, $field->type);
                $data[$field->name] = $this->mapper->toPHPValue($ctx, $value);
            } catch (Mapper\ConversionError $e) {
                throw new ScanError(sprintf(
                    'Error while mapping property "%s" of class "%s"',
                    $field->name,
                    $class
                ), 0, $e);
            }
        }

        return $data;
    }
}
