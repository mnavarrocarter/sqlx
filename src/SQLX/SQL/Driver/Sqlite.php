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

namespace MNC\SQLX\SQL\Driver;

use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Driver;

final class Sqlite implements Driver, Dialect
{
    public function getName(): string
    {
        return self::SQLITE;
    }

    public function quoteTable(string $table): string
    {
        return $table;
    }

    public function quoteColumn(string $column): string
    {
        return $column;
    }

    public function cleanValue(mixed $value): mixed
    {
        return $value;
    }
}
