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
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Driver;
use MNC\SQLX\SQL\Statement;
use PDO;
use PDOException;

/**
 * PDOWrapper is both a connection and a driver for databases in PHP.
 */
final class PDOWrapper implements Connection, Driver\Aware
{
    private PDO $pdo;
    private Driver $driver;

    /**
     * We recommend calling one of the static constructors.
     *
     * This is because we attempt to guess the driver being used to provide
     * additional features to this library.
     *
     * @internal
     */
    public function __construct(PDO $pdo, Driver $driver)
    {
        $this->pdo = $pdo;
        $this->driver = $driver;
    }

    public static function from(PDO $pdo): PDOWrapper
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $dialect = match ($driver) {
            Driver::PGSQL => new Driver\Postgres(),
            Driver::MYSQL => new Driver\MySQL(),
            Driver::SQLITE => new Driver\Sqlite(),
            default => new Driver\Generic(),
        };

        return new self($pdo, $dialect);
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

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * @throws ExecutionError
     */
    private function doExecute(Context $ctx, Statement $statement): PDOStmt
    {
        $dialect = $ctx->value(Dialect::KEY) ?? $this->getDialect();

        try {
            $stmt = $this->pdo->prepare($statement->getSQL($dialect));
            $stmt->execute($statement->getParameters($dialect));
        } catch (PDOException $e) {
            throw new ExecutionError('Query execution error', 0, $e);
        }

        return new PDOStmt($this->pdo, $stmt);
    }

    private function getDialect(): Dialect
    {
        if ($this->driver instanceof Dialect) {
            return $this->driver;
        }

        return new Dialect\Noop();
    }
}
