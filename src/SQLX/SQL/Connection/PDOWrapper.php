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

use Castor\Context;
use LogicException;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Statement;
use PDO;
use PDOException;

/**
 * PDOWrapper is both a connection and a driver for databases in PHP.
 */
final class PDOWrapper implements Connection, DialectAware, Dialect
{
    private PDO $pdo;

    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function build(string $uri): PDOWrapper
    {
        throw new LogicException('Not Implemented');
    }

    public static function from(PDO $pdo): PDOWrapper
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new self($pdo);
    }

    /**
     * @throws ExecutionError
     */
    public function execute(Context $ctx, Statement $statement): Result
    {
        return $this->doExecute($ctx, $statement);
    }

    public function query(Context $ctx, Statement $statement): Rows
    {
        return $this->doExecute($ctx, $statement);
    }

    /**
     * @throws ExecutionError
     */
    final public function doExecute(Context $ctx, Statement $statement): PDOStmt
    {
        $dialect = $ctx->value(Dialect::KEY) ?? $this->getDialect();

        try {
            $stmt = $this->pdo->prepare($statement->getSQL($dialect));
        } catch (PDOException $e) {
            throw new ExecutionError('Query syntax error', 0, $e);
        }

        try {
            $stmt->execute($statement->getParameters($dialect));
        } catch (PDOException $e) {
            throw new ExecutionError('Query execution error', 0, $e);
        }

        return new PDOStmt($this->pdo, $stmt);
    }

    public function getDialect(): Dialect
    {
        return $this;
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
