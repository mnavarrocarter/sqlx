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
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MNC\SQLX\SQL\Query\Delete
 * @covers \MNC\SQLX\SQL\Query\Parts\Where
 */
class DeleteTest extends TestCase
{
    public function testRawClause(): void
    {
        $driver = $this->createStub(Driver::class);
        $query = Delete::from('users')->andWhere('id = ?', 21);

        $sql = $query->getSQL($driver);
        $params = $query->getParameters($driver);

        $this->assertSame('DELETE FROM users WHERE id = ?;', $sql);
        $this->assertCount(1, $params);
        $this->assertSame([21], $params);
    }

    public function testComplexCause(): void
    {
        $driver = $this->createStub(Driver::class);
        $query = Delete::from('users')
            ->andWhere(new AndN(
                Comp::in('account_id', 1, 2, 3, 4, 5),
                Comp::eq('active', true),
                Comp::gt('created_at', '2022-01-23')
            ))
        ;

        $sql = $query->getSQL($driver);
        $params = $query->getParameters($driver);

        $this->assertSame('DELETE FROM users WHERE account_id IN (?, ?, ?, ?, ?) AND active = ? AND created_at > ?;', $sql);
        $this->assertCount(7, $params);
        $this->assertSame([1, 2, 3, 4, 5, true, '2022-01-23'], $params);
    }
}
