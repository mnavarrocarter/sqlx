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

final class Delete implements Statement
{
    use Parts\Where;

    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->where = [];
    }

    public static function from(string $table): Delete
    {
        return new self($table);
    }

    public function andWhere(Clause|string $clause, mixed ...$args): Delete
    {
        $this->addAndWhere($clause, ...$args);

        return $this;
    }

    public function orWhere(Clause|string $clause, mixed ...$args): Delete
    {
        $this->addOrWhere($clause, ...$args);

        return $this;
    }

    public function getSQL(Dialect $driver): string
    {
        return sprintf(
            'DELETE FROM %s %s;',
            $driver->quoteTable($this->table),
            $this->getWhereSQL($driver)
        );
    }

    public function getParameters(Dialect $driver): array
    {
        return $this->getWhereParameters($driver);
    }
}
