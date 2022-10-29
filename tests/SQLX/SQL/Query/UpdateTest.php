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
 * @coversNothing
 */
class UpdateTest extends TestCase
{
    public function testRawWhere(): void
    {
        $driver = $this->createStub(Driver::class);

        $query = Update::table('users')
            ->andWhere('account_id = ?', 22)
            ->set([
                'disabled' => false,
            ])
        ;

        $sql = $query->getSQL($driver);
        $params = $query->getParameters($driver);

        $this->assertSame('UPDATE users SET disabled = ? WHERE account_id = ?;', $sql);
        $this->assertCount(2, $params);
        $this->assertSame([false, 22], $params);
    }

    public function testAndRaw(): void
    {
        $driver = $this->createStub(Driver::class);

        $query = Update::table('users')
            ->andWhere('account_id = ?', 22)
            ->andWhere('login_attempts > ?', 5)
            ->set([
                'disabled' => true,
                'login_attempts' => 0,
            ])
        ;

        $sql = $query->getSQL($driver);
        $params = $query->getParameters($driver);

        $this->assertSame('UPDATE users SET disabled = ?, login_attempts = ? WHERE account_id = ? AND login_attempts > ?;', $sql);
        $this->assertCount(4, $params);
        $this->assertSame([true, 0, 22, 5], $params);
    }
}
