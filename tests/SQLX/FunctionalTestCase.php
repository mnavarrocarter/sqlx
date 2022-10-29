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
use MNC\SQLX\SQL\Connection\ExecutionError;
use MNC\SQLX\SQL\Connection\PDOWrapper;
use MNC\SQLX\SQL\Query\Raw;
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

    protected function assertRecordExists(string $table, string $id, mixed $v): void
    {
        $conn = $this->getConnection();
        $query = Raw::query(sprintf('SELECT EXISTS(SELECT %s FROM %s WHERE %s = ?)', $id, $table, $id), $v);

        try {
            $rows = $conn->query(Context\nil(), $query);
        } catch (ExecutionError $e) {
            $this->fail('Failed to execute assertion: '.$e->getMessage());
        }

        $val = null;
        $rows->scan($val);

        $this->assertContains($val, ['1', 1, true], sprintf('Record where %s = %s does exist in %s table', $id, $v, $table));
    }

    protected function assertRecordNotExists(string $table, string $id, mixed $v): void
    {
        $conn = $this->getConnection();
        $query = Raw::query(sprintf('SELECT EXISTS(SELECT %s FROM %s WHERE %s = ?)', $id, $table, $id), $v);

        try {
            $rows = $conn->query(Context\nil(), $query);
        } catch (ExecutionError $e) {
            $this->fail('Failed to execute assertion: '.$e->getMessage());
        }

        $val = null;
        $rows->scan($val);

        $this->assertContains($val, ['0', 0, false], sprintf('Record where %s %s does not exist in %s table', $id, $v, $table));
    }

    /**
     * @param mixed $id
     */
    protected function assertRecordContains(string $table, string $id, mixed $v, array $data): void
    {
        $conn = $this->getConnection();
        $query = Raw::query(sprintf('SELECT * FROM %s WHERE %s = ?', $table, $id), $v);

        try {
            $rows = $conn->query(Context\nil(), $query);
        } catch (ExecutionError $e) {
            $this->fail('Failed to execute assertion: '.$e->getMessage());
        }

        $row = [];
        $rows->scanAssoc($row);

        foreach ($data as $key => $datum) {
            $this->assertArrayHasKey($key, $row, sprintf('Column %s does not exist in result', $key));
            $this->assertSame($row[$key], $datum, sprintf('Value of column %s is not the expected', $key));
        }
    }
}
