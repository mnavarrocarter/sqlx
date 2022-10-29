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

namespace MNC\SQLX\Engine\Operator;

use Castor\Context;
use LogicException;
use MNC\SQLX\Engine\Operator;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Query\Comp;
use MNC\SQLX\SQL\Query\Delete;
use MNC\SQLX\SQL\Query\Insert;
use MNC\SQLX\SQL\Query\Update;

/**
 * The Immediate operator executes operation immediately on the connection.
 *
 * This as opposed to other operators that can accumulate and flush write
 * operations to the database.
 */
final class Immediate implements Operator
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Context $ctx, object $cmd): Connection\Result
    {
        $query = null;

        if ($cmd instanceof Cmd\Insert) {
            $query = Insert::into($cmd->table)->values($cmd->values);
        }

        if ($cmd instanceof Cmd\Update) {
            $query = Update::table($cmd->table)->set($cmd->values);
            foreach ($cmd->where as $column => $value) {
                $query->andWhere(Comp::eq($column, $value));
            }
        }

        if ($cmd instanceof Cmd\Delete) {
            $query = Delete::from($cmd->table);

            foreach ($cmd->where as $column => $value) {
                $query->andWhere(Comp::eq($column, $value));
            }
        }

        if (null === $query) {
            throw new ExecutionError(sprintf('Invalid command %s', get_class($cmd)));
        }

        try {
            return $this->conn->execute($ctx, $query);
        } catch (Connection\ExecutionError $e) {
            throw new ExecutionError('Error executing statement', 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function query(Context $ctx): Connection\Rows
    {
        throw new LogicException('Not Implemented');
    }
}
