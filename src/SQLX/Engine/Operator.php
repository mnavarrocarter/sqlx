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

namespace MNC\SQLX\Engine;

use Castor\Context;
use MNC\SQLX\Engine\Operator\ExecutionError;
use MNC\SQLX\SQL\Connection\Result;
use MNC\SQLX\SQL\Connection\Rows;

interface Operator
{
    /**
     * Executes a predefined operation in a database table.
     *
     * @throws ExecutionError if there is an error executing the command
     */
    public function execute(Context $ctx, object $cmd): Result;

    /**
     * Executes a predefines query in the database.
     *
     * @throws ExecutionError if there is an error executing the query
     */
    public function query(Context $ctx): Rows;
}
