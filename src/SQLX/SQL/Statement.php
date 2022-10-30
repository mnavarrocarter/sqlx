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

interface Statement
{
    /**
     * Returns the SQL from the statement.
     *
     * The dialect quotes reserved keywords accordingly.
     */
    public function getSQL(Dialect $dialect): string;

    /**
     * Returns the parameters from the statement.
     *
     * The dialect handles values accordingly.
     */
    public function getParameters(Dialect $dialect): array;
}
