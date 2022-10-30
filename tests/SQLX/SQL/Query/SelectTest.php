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
 * @covers \MNC\SQLX\SQL\Query\OrN
 * @covers \MNC\SQLX\SQL\Query\Parts\Where
 * @covers \MNC\SQLX\SQL\Query\Raw
 * @covers \MNC\SQLX\SQL\Query\Select
 */
class SelectTest extends TestCase
{
    public function testFullSelectQuery(): void
    {
        $dialect = new Dialect\Noop();
        $query = Select::all()
            ->from('user')
            ->andWhere('status = ?', 'active')
            ->addOrderBy('last_updated', 'ASC')
            ->setLimit(1)
            ->setOffset(20)
        ;

        $sql = $query->getSQL($dialect);
        $params = $query->getParameters($dialect);

        $this->assertSame('SELECT * FROM user WHERE status = ? ORDER BY last_updated ASC LIMIT ?, OFFSET ?;', $sql);
        $this->assertSame(['active', 1, 20], $params);
    }
}
