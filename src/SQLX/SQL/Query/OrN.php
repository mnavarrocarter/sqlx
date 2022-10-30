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

final class OrN implements Clause
{
    /**
     * @var Clause[]
     */
    public array $clauses;

    public function __construct(Clause ...$clauses)
    {
        $this->clauses = $clauses;
    }

    public function getSQL(Dialect $dialect): string
    {
        $or = [];
        foreach ($this->clauses as $clause) {
            $or[] = $clause->getSQL($dialect);
        }

        $sql = implode(' OR ', $or);

        if (count($or) > 1) {
            return '('.$sql.')';
        }

        if (1 === count($or)) {
            return 'OR '.$sql;
        }

        return $sql;
    }

    public function getParameters(Dialect $dialect): array
    {
        $params = [];
        foreach ($this->clauses as $clause) {
            $params[] = $clause->getParameters($dialect);
        }

        return array_merge(...$params);
    }
}
