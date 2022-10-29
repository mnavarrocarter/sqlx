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
use MNC\SQLX\SQL\Driver;
use MNC\SQLX\SQL\Statement;
use PDO;
use PDOException;

/**
 * PDOWrapper is both a connection and a driver for databases in PHP.
 */
final class PDOWrapper implements Connection, Driver
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
        $stmt = $this->pdo->prepare($statement->getSQL($this));

        try {
            $stmt->execute($statement->getParameters($this));
        } catch (PDOException $e) {
            throw new ExecutionError('Error while executing statement', 0, $e);
        }

        return new PDOStmt($this->pdo, $stmt);
    }
}
