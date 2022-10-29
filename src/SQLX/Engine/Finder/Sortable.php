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

interface Sortable
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    /**
     * Adds a clause to sort by.
     */
    public function sortBy(string $field, string $order = self::ORDER_ASC): Sortable;
}
