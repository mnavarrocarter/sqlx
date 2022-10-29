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

use Castor\Context;
use MNC\SQLX\SQL\Connection\ExecutionError;
use MNC\SQLX\SQL\Connection\Result;
use MNC\SQLX\SQL\Connection\Rows;

interface Connection
{
    /**
     * Executes a state change statement.
     *
     * @throws ExecutionError
     */
    public function execute(Context $ctx, Statement $statement): Result;

    /**
     * Executes a query statement.
     *
     * @throws ExecutionError
     */
    public function query(Context $ctx, Statement $statement): Rows;
}
