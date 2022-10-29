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

use MNC\SQLX\SQL\Driver;

final class AndN implements Clause
{
    /**
     * @var Clause[]
     */
    private array $clauses;

    public function __construct(Clause ...$clauses)
    {
        $this->clauses = $clauses;
    }

    public function getSQL(Driver $driver): string
    {
        $and = [];
        foreach ($this->clauses as $clause) {
            $and[] = $clause->getSQL($driver);
        }

        $sql = implode(' AND ', $and);

        if (count($and) > 1) {
            return '('.$sql.')';
        }

        if (1 === count($and)) {
            return 'AND '.$sql;
        }

        return $sql;
    }

    public function getParameters(Driver $driver): array
    {
        $params = [];
        foreach ($this->clauses as $clause) {
            $params[] = $clause->getParameters($driver);
        }

        return array_merge(...$params);
    }
}
