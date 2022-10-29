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
use MNC\SQLX\SQL\Statement;

class Insert implements Statement
{
    use Parts\Values;

    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function into(string $table): Insert
    {
        return new self($table);
    }

    /**
     * @param array<string,mixed> $values
     *
     * @throws InvalidQuery if the number of values does not match the previously set columns
     */
    public function values(array $values): Insert
    {
        $this->addValues($values);

        return $this;
    }

    public function getSQL(Driver $driver): string
    {
        return sprintf(
            'INSERT INTO %s %s;',
            $this->table,
            $this->getSQLForValues($driver)
        );
    }

    public function getParameters(Driver $driver): array
    {
        return $this->getValueParameters($driver);
    }
}
