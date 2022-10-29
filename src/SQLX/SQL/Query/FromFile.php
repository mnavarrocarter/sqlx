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

/**
 * FromFile loads a SQL Statement from a file.
 */
final class FromFile implements Statement
{
    private string $sql;

    private function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @throws InvalidQuery if the file cannot be read
     */
    public static function open(string $filename): FromFile
    {
        $sql = @file_get_contents($filename);
        if (false === $sql) {
            throw new InvalidQuery(error_get_last()['message'] ?? 'Unknown error');
        }

        return new self($sql);
    }

    public function getSQL(Driver $driver): string
    {
        return $this->sql;
    }

    public function getParameters(Driver $driver): array
    {
        return [];
    }
}
