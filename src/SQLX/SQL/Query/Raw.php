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

final class Raw implements Clause, Statement
{
    private string $raw;
    private array $params;

    public function __construct(string $raw, array $params = [])
    {
        $this->raw = $raw;
        $this->params = $params;
    }

    public static function query(string $sql, mixed ...$args): Raw
    {
        return new self($sql, $args);
    }

    public function getSQL(Driver $driver): string
    {
        return $this->raw;
    }

    public function getParameters(Driver $driver): array
    {
        return $this->params;
    }
}
