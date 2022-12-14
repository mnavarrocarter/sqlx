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

namespace MNC\SQLX\SQL\Connection;

interface Result
{
    /**
     * Returns the last inserted row ID.
     *
     * If the database driver does not support this capability, an
     * empty string is returned.
     */
    public function getLastInsertedId(): string;

    /**
     * Returns the number of the affected rows in the last operation.
     */
    public function getAffectedRows(): int;
}
