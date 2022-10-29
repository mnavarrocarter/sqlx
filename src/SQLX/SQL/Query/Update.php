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

final class Update implements Statement
{
    use Parts\Where;
    use Parts\Values;

    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function table(string $table): Update
    {
        return new self($table);
    }

    public function andWhere(Clause|string $clause, mixed ...$args): Update
    {
        $this->addAndWhere($clause, ...$args);

        return $this;
    }

    public function orWhere(Clause|string $clause, mixed ...$args): Update
    {
        $this->addOrWhere($clause, ...$args);

        return $this;
    }

    public function set(array $data): Update
    {
        $this->addSet($data);

        return $this;
    }

    public function getSQL(Dialect $driver): string
    {
        return sprintf(
            'UPDATE %s %s %s;',
            $driver->quoteTable($this->table),
            $this->getSQLForSet($driver),
            $this->getWhereSQL($driver)
        );
    }

    public function getParameters(Dialect $driver): array
    {
        return array_merge(
            $this->getValueParameters($driver),
            $this->getWhereParameters($driver)
        );
    }
}
