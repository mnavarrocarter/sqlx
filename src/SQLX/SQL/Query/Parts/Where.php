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

namespace MNC\SQLX\SQL\Query\Parts;

use MNC\SQLX\SQL\Driver;
use MNC\SQLX\SQL\Query\AndN;
use MNC\SQLX\SQL\Query\Clause;
use MNC\SQLX\SQL\Query\OrN;
use MNC\SQLX\SQL\Query\Raw;

trait Where
{
    /**
     * @var Clause[]
     */
    private array $where = [];

    private function addAndWhere(Clause|string $clause, mixed ...$args): void
    {
        if (is_string($clause)) {
            $clause = new Raw($clause, $args);
        }

        if ([] === $this->where) {
            $this->where[] = $clause;

            return;
        }

        $this->where[] = new AndN($clause);
    }

    private function addOrWhere(Clause|string $clause, mixed ...$args): void
    {
        if (is_string($clause)) {
            $clause = new Raw($clause, $args);
        }

        if ([] === $this->where) {
            $this->where[] = $clause;

            return;
        }

        $this->where[] = new OrN($clause);
    }

    private function getWhereSQL(Driver $driver): string
    {
        $where = [];
        foreach ($this->where as $clause) {
            $part = $clause->getSQL($driver);
            if (count($this->where) > 1) {
                $where[] = $part;

                continue;
            }

            if ($clause instanceof AndN || $clause instanceof OrN) {
                $part = trim($part, '()');
            }

            $where[] = $part;
        }

        return 'WHERE '.implode(' ', $where);
    }

    private function getWhereParameters(Driver $driver): array
    {
        $params = [];
        foreach ($this->where as $clause) {
            $params[] = $clause->getParameters($driver);
        }

        return array_merge(...$params);
    }
}
