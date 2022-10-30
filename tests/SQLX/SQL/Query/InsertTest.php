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

use MNC\SQLX\SQL\Dialect;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MNC\SQLX\SQL\Query\AndN
 * @covers \MNC\SQLX\SQL\Query\Comp
 * @covers \MNC\SQLX\SQL\Query\Insert
 * @covers \MNC\SQLX\SQL\Query\OrN
 * @covers \MNC\SQLX\SQL\Query\Parts\Values
 * @covers \MNC\SQLX\SQL\Query\Raw
 */
class InsertTest extends TestCase
{
    public function testSingleSetOfValues(): void
    {
        $dialect = new Dialect\Noop();

        $query = Insert::into('users')->values([
            'id' => '77fdf658-5fd9-419f-a6ca-e960cd9c8daa',
            'name' => 'John Doe',
            'username' => 'jdoe',
            'password' => 'secret',
        ]);

        $sql = $query->getSQL($dialect);
        $params = $query->getParameters($dialect);

        $this->assertSame('INSERT INTO users (id, name, username, password) VALUES (?, ?, ?, ?);', $sql);
        $this->assertCount(4, $params);
        $this->assertSame(['77fdf658-5fd9-419f-a6ca-e960cd9c8daa', 'John Doe', 'jdoe', 'secret'], $params);
    }

    public function testMultipleValues(): void
    {
        $dialect = new Dialect\Noop();

        $query = Insert::into('users')
            ->values([
                'id' => '77fdf658-5fd9-419f-a6ca-e960cd9c8daa',
                'name' => 'John Doe',
                'username' => 'jdoe',
                'password' => 'secret',
            ])
            ->values([
                'id' => '07d22215-7966-49fa-a86a-f666e35735ae',
                'name' => 'Anna Doe',
                'username' => 'adoe',
                'password' => 'secret',
            ])
        ;

        $sql = $query->getSQL($dialect);
        $params = $query->getParameters($dialect);

        $this->assertSame('INSERT INTO users (id, name, username, password) VALUES (?, ?, ?, ?), (?, ?, ?, ?);', $sql);
        $this->assertCount(8, $params);
    }
}
