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

namespace MNC\SQLX\SQL\Connection;

class PDOResult implements Result
{
    private \PDO $pdo;
    private \PDOStatement $stmt;

    public function __construct(\PDO $pdo, \PDOStatement $stmt)
    {
        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }

    public function affectedRows(): int
    {
        return $this->stmt->rowCount();
    }

    public function getLastInsertedId(): string
    {
        return (string) $this->pdo->lastInsertId();
    }
}
