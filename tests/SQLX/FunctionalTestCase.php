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

namespace MNC\SQLX;

use Castor\Context;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Connection\PDOWrapper;
use MNC\SQLX\SQL\Statement;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    /**
     * @var array{0: string, 1: null|string, 2: null|string, 3: null|array}
     */
    protected array $params = ['sqlite::memory', null, null, null];

    private ?PDO $pdo = null;
    private ?Connection $conn = null;

    /**
     * @throws Connection\ExecutionError
     */
    public function setUp(): void
    {
        $this->pdo = new PDO(...$this->params);
        $conn = $this->getConnection();

        foreach ($this->getSetupStatements() as $statement) {
            $conn->execute(Context\nil(), $statement);
        }
    }

    /**
     * @throws Connection\ExecutionError
     */
    public function tearDown(): void
    {
        $conn = $this->getConnection();
        foreach ($this->getTearDownStatements() as $statement) {
            $conn->execute(Context\nil(), $statement);
        }

        $this->conn = null;
        $this->pdo = null;
    }

    /**
     * @return iterable<int,Statement>
     */
    protected function getSetupStatements(): iterable
    {
        return [];
    }

    /**
     * @return iterable<int,Statement>
     */
    protected function getTearDownStatements(): iterable
    {
        return [];
    }

    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function getConnection(): Connection
    {
        if (null === $this->conn) {
            $this->conn = PDOWrapper::from($this->getPDO());
        }

        return $this->conn;
    }
}
