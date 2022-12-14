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

use Generator;
use IteratorAggregate;
use PDO;
use PDOStatement;

final class PDOStmt implements Result, Rows, IteratorAggregate
{
    private PDO $pdo;
    private PDOStatement $stmt;

    public function __construct(PDO $pdo, PDOStatement $stmt)
    {
        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }

    public function affectedRows(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getLastInsertedId(): string
    {
        $id = $this->pdo->lastInsertId();
        if (!is_string($id)) {
            return '';
        }

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRows(): int
    {
        return $this->stmt->rowCount();
    }

    public function scanAssoc(array &$value): void
    {
        $tmp = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($tmp)) {
            $tmp = [];
        }

        $value = $tmp;
    }

    public function scan(mixed &...$values): void
    {
        $fetched = $this->stmt->fetch(PDO::FETCH_NUM);
        array_walk($values, static function (mixed &$val, $i) use ($fetched) {
            $val = $fetched[$i] ?? null;
        });
    }

    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    public function getIterator(): Generator
    {
        while (true) {
            $assoc = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($assoc)) {
                break;
            }

            yield $assoc;
        }
    }
}
