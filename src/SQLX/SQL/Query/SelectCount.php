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

use MNC\SQLX\Engine\Finder\Filterable;
use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Statement;

final class SelectCount implements Statement, Filterable
{
    use Parts\Where;

    private string $table;

    public function __construct(string $table, Clause ...$where)
    {
        $this->table = $table;
        $this->where = $where;
    }

    public function andWhere(Clause|string $clause, mixed ...$args): SelectCount
    {
        $this->addAndWhere($clause, ...$args);

        return $this;
    }

    public function orWhere(Clause|string $clause, mixed ...$args): SelectCount
    {
        $this->addOrWhere($clause, ...$args);

        return $this;
    }

    public function getSQL(Dialect $dialect): string
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s', $dialect->quoteTable($this->table));
        if ([] !== $this->where) {
            $sql .= ' '.$this->getWhereSQL($dialect);
        }

        return $sql;
    }

    public function getParameters(Dialect $dialect): array
    {
        return $this->getWhereParameters($dialect);
    }
}
