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

namespace MNC\SQLX\Engine\Mapper;

use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;

class FindClass
{
    public Connection $connection;
    public Tracker $tracker;
    public string $classname;

    public function __construct(Connection $connection, Tracker $tracker, string $classname)
    {
        $this->connection = $connection;
        $this->tracker = $tracker;
        $this->classname = $classname;
    }
}
