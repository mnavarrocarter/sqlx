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

namespace MNC\SQLX\SQL\Query;

use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Statement;

final class Select implements Statement
{
    use Parts\Where;

    private string $table;

    private int $offset = 0;
    private int $limit = 0;

    /**
     * @var array<string,null|string>
     */
    private array $orderBy = [];

    /**
     * @var Column[]
     */
    private array $columns;

    public function __construct()
    {
        $this->table = '';
        $this->columns = [];
    }

    public static function all(string ...$columns): Select
    {
        $select = new self();
        foreach ($columns as $column) {
            $select->col($column);
        }

        return $select;
    }

    public function from(string $table): Select
    {
        $this->table = $table;

        return $this;
    }

    public function col(Column|string $name, mixed ...$as): Select
    {
        if (is_string($name)) {
            $name = new Column($name, $as[0] ?? '');
        }

        return $this;
    }

    public function andWhere(Clause|string $clause, mixed ...$args): Select
    {
        $this->addAndWhere($clause, ...$args);

        return $this;
    }

    public function orWhere(Clause|string $clause, mixed ...$args): Select
    {
        $this->addOrWhere($clause, ...$args);

        return $this;
    }

    /**
     * @return $this
     */
    public function addOrderBy(string $column, ?string $order = null): Select
    {
        $this->orderBy[$column] = $order;

        return $this;
    }

    public function setLimit(int $limit = 0): Select
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset = 0): Select
    {
        $this->offset = $offset;

        return $this;
    }

    public function getSQL(Dialect $dialect): string
    {
        $sql = 'SELECT '.$this->getColumns($dialect);

        if ('' !== $this->table) {
            $sql .= ' FROM '.$dialect->quoteTable($this->table);
        }

        if ([] !== $this->where) {
            $sql = sprintf('%s %s', $sql, $this->getWhereSQL($dialect));
        }

        $ob = $this->getOrderBy($dialect);
        if ('' !== $ob) {
            $sql .= ' '.$ob;
        }

        $lo = $this->getLimitAndOffset($dialect);
        if ('' !== $lo) {
            $sql .= ' '.$lo;
        }

        return $sql.';';
    }

    public function getParameters(Dialect $dialect): array
    {
        $where = $this->getWhereParameters($dialect);

        if ($this->limit > 0) {
            $where[] = $this->limit;
        }

        if ($this->offset > 0) {
            $where[] = $this->offset;
        }

        return $where;
    }

    /**
     * Transforms this select query into a count query.
     */
    public function toCount(): SelectCount
    {
        return new SelectCount($this->table, ...$this->where);
    }

    private function getColumns(Dialect $driver): string
    {
        if ([] === $this->columns) {
            return '*';
        }

        return implode(', ', array_map(static fn (Column $col): string => $col->getSQL($driver), $this->columns));
    }

    private function getLimitAndOffset(Dialect $driver): string
    {
        $clauses = [];
        if ($this->limit > 0) {
            $clauses[] = 'LIMIT ?';
        }

        if ($this->offset > 0) {
            $clauses[] = 'OFFSET ?';
        }

        if ([] === $clauses) {
            return '';
        }

        return implode(', ', $clauses);
    }

    private function getOrderBy(Dialect $driver): string
    {
        if ([] === $this->orderBy) {
            return '';
        }

        $clauses = [];
        foreach ($this->orderBy as $col => $order) {
            $col = $driver->quoteColumn($col);
            if (null === $order) {
                $clauses[] = $col;

                continue;
            }

            $clauses[] = sprintf('%s %s', $col, $order);
        }

        return sprintf('ORDER BY %s', implode(', ', $clauses));
    }
}
