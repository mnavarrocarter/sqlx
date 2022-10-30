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

    public function getSQL(Dialect $dialect): string
    {
        return sprintf(
            'INSERT INTO %s %s;',
            $dialect->quoteTable($this->table),
            $this->getSQLForValues($dialect)
        );
    }

    public function getParameters(Dialect $dialect): array
    {
        return $this->getValueParameters($dialect);
    }
}
