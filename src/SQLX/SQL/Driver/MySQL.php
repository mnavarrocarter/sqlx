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

final class MySQL implements Driver, Dialect
{
    private const QUOTE = '`';

    public function getName(): string
    {
        return self::MYSQL;
    }

    public function quoteTable(string $table): string
    {
        return self::QUOTE.$table.self::QUOTE;
    }

    public function quoteColumn(string $column): string
    {
        return self::QUOTE.$column.self::QUOTE;
    }

    public function cleanValue(mixed $value): mixed
    {
        return $value;
    }
}
