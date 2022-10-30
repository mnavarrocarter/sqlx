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
use MNC\SQLX\Engine\Finder;
use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\PropertyAccessor\Store;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Connection\Rows;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Query\AndN;
use MNC\SQLX\SQL\Query\Clause;
use MNC\SQLX\SQL\Query\Comp;
use MNC\SQLX\SQL\Query\OrN;
use MNC\SQLX\SQL\Query\Raw;
use MNC\SQLX\SQL\Query\Select;

final class ForEntities implements Finder, IteratorAggregate
{
    private Select $query;
    private Connection $connection;
    private Context $ctx;
    private Metadata $metadata;
    private Mapper $mapper;
    private Tracker $tracker;
    private Store $assessor;

    /**
     * @param Store $accessor
     */
    public function __construct(
        Context $ctx,
        Select $query,
        Connection $connection,
        Metadata $metadata,
        Mapper $mapper,
        Tracker $tracker,
        PropertyAccessor\Store $accessor
    ) {
        $this->ctx = $ctx;
        $this->query = $query;
        $this->connection = $connection;
        $this->mapper = $mapper;
        $this->metadata = $metadata;
        $this->tracker = $tracker;
        $this->assessor = $accessor;
    }

    /**
     * {@inheritDoc}
     */
    public function andWhere(Clause|string $clause, ...$args): ForEntities
    {
        try {
            $this->mapClause($clause);
        } catch (Mapper\ConversionError $e) {
            throw new FinderError('Could not map clause', 0, $e);
        }
        $this->query->andWhere($clause, ...$args);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhere(Clause|string $clause, ...$args): ForEntities
    {
        try {
            $this->mapClause($clause);
        } catch (Mapper\ConversionError $e) {
            throw new FinderError('Could not map clause', 0, $e);
        }
        $this->query->orWhere($clause, ...$args);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sortBy(string $field, string $order = self::ORDER_ASC): ForEntities
    {
        $f = $this->metadata->getFieldByProp($field);
        if ($f instanceof Metadata\Field) {
            $field = $f->column;
        }
        $this->query->addOrderBy($field, $order);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function slice(int $offset, int $length = 0): ForEntities
    {
        $this->query->setOffset($offset);
        $this->query->setLimit($length);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function one(): object
    {
        $this->query->setLimit(2);

        $rows = $this->rows();

        $first = null;
        $second = null;

        try {
            $rows->scan($first, $second);
        } catch (Connection\ScanError $e) {
            throw new FinderError('Could not scan object', 0, $e);
        }

        if (!is_object($first)) {
            throw new NotFoundError('Record not found');
        }

        if (is_object($second)) {
            throw new MoreThanOneError('More than one record found');
        }

        return $first;
    }

    /**
     * {@inheritDoc}
     */
    public function first(): object
    {
        $this->query->setLimit(1);
        $object = null;
        $rows = $this->rows();

        try {
            $rows->scan($object);
        } catch (Connection\ScanError $e) {
            throw new FinderError('Could not scan object', 0, $e);
        }

        if (!is_object($object)) {
            throw new NotFoundError('Record not found');
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function nth(int $n): ?object
    {
        $this->query->setLimit(1);
        $this->query->setOffset($n);

        $rows = $this->rows();

        $object = null;

        try {
            $rows->scan($object);
        } catch (Connection\ScanError $e) {
            throw new FinderError('Could not scan object', 0, $e);
        }

        return $object;
    }

    /**
     * @throws FinderError
     */
    public function count(): int
    {
        $rows = $this->query(true);

        $count = 0;

        try {
            $rows->scan($count);
        } catch (Connection\ScanError $e) {
            throw new FinderError('Scanning error', 0, $e);
        }

        return (int) $count;
    }

    /**
     * {@inheritDoc}
     */
    public function rows(): Rows
    {
        $rows = $this->query();

        return new HydratableRows(
            $this->ctx,
            $rows,
            $this->metadata,
            $this->mapper,
            $this->tracker,
            $this->assessor
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Generator
    {
        $rows = $this->rows();

        while (true) {
            $object = null;

            try {
                $rows->scan($object);
            } catch (Connection\ScanError $e) {
                throw new FinderError('Error while scanning object', 0, $e);
            }
            if (null === $object) {
                break;
            }

            yield $this;
        }
    }

    /**
     * Executes a query.
     *
     * @param bool $count Whether this is a count query
     *
     * @throws FinderError
     */
    private function query(bool $count = false): Rows
    {
        $query = $count ? $this->query->toCount() : $this->query;

        try {
            return $this->connection->query($this->ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new FinderError('Query error', 0, $e);
        }
    }

    /**
     * @throws Mapper\ConversionError
     */
    private function mapClause(Clause|string $clause): void
    {
        if (!$clause instanceof Clause || $clause instanceof Raw) {
            return;
        }

        if ($clause instanceof AndN || $clause instanceof OrN) {
            foreach ($clause->clauses as $c) {
                $this->mapClause($c);
            }
        }

        if ($clause instanceof Comp) {
            $field = $this->metadata->getFieldByProp($clause->column);
            if ($field instanceof Metadata\Field) {
                $clause->column = $field->column;
            }

            array_walk($clause->params, function (mixed &$param) {
                $param = $this->mapper->toDatabaseValue($this->ctx, $param);
            });
        }
    }
}
