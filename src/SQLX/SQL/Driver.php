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

namespace MNC\SQLX\SQL;

/**
 * A driver represents a particular engine driver.
 *
 * Drivers can have their own way of mapping certain values by implementing
 * the Mapper interface.
 *
 * Also, they can implement Dialect, to mutate certain elements of SQL queries
 * to fit their special syntax.
 */
interface Driver
{
    public const GENERIC = 'generic';
    public const PGSQL = 'pgsql';
    public const MYSQL = 'mysql';
    public const SQLITE = 'sqlite';

    /**
     * Returns the driver name.
     */
    public function getName(): string;
}
