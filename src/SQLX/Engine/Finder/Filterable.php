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

namespace MNC\SQLX\Engine\Finder;

use MNC\SQLX\SQL\Query\Clause;

interface Filterable
{
    /**
     * Adds a where clause with an AND if necessary.
     */
    public function andWhere(Clause|string $clause, mixed ...$args): Filterable;

    /**
     * Adds a where clause with an OR if necessary.
     */
    public function orWhere(Clause|string $clause, mixed ...$args): Filterable;
}
