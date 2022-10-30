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

namespace MNC\SQLX\SQL\Mapper;

use Castor\Context;
use MNC\SQLX\SQL\Driver;
use MNC\SQLX\SQL\Mapper;

/**
 * Passes driver information to the next mappers.
 */
final class WithDriver implements Mapper
{
    private Mapper $next;
    private Driver $driver;

    public function __construct(Mapper $next, Driver $driver)
    {
        $this->next = $next;
        $this->driver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed
    {
        return $this->next->toPHPValue(
            Driver\withDriver($ctx, $this->driver),
            $value
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toDatabaseValue(Context $ctx, mixed $value): mixed
    {
        return $this->next->toDatabaseValue(
            Driver\withDriver($ctx, $this->driver),
            $value
        );
    }
}
