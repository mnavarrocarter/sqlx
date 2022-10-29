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

use MNC\SQLX\Engine\Finder\Filterable;
use MNC\SQLX\Engine\Finder\FinderError;
use MNC\SQLX\Engine\Finder\MoreThanOneError;
use MNC\SQLX\Engine\Finder\NotFoundError;
use MNC\SQLX\Engine\Finder\Sliceable;
use MNC\SQLX\Engine\Finder\Sortable;
use MNC\SQLX\SQL\Connection\Rows;
use MNC\SQLX\SQL\Query\Clause;
use Traversable;

interface Finder extends Filterable, Sortable, Sliceable, Traversable
{
    /**
     * Finds exactly one record.
     *
     * @throws FinderError      if there is a problem finding the record
     * @throws NotFoundError    if the record is not found
     * @throws MoreThanOneError if there is more than one result
     */
    public function one(): object;

    /**
     * Finds the first record.
     *
     * If there is no records, an exception is thrown
     *
     * @throws FinderError   if there is a problem finding the record
     * @throws NotFoundError if there is no first record
     */
    public function first(): object;

    /**
     * Finds the nth record.
     *
     * If no record is found, then null is returned
     *
     * @throws FinderError if there is a problem finding the record
     */
    public function nth(int $n): ?object;

    /**
     * Gets the rows object.
     *
     * @throws FinderError if there is a problem finding the record
     */
    public function rows(): Rows;

    /**
     * {@inheritDoc}
     */
    public function andWhere(Clause|string $clause, mixed ...$args): Finder;

    /**
     * {@inheritDoc}
     */
    public function orWhere(Clause|string $clause, mixed ...$args): Finder;

    /**
     * {@inheritDoc}
     */
    public function sortBy(string $field, string $order = self::ORDER_ASC): Finder;

    /**
     * {@inheritDoc}
     */
    public function slice(int $offset, int $length = 0): Finder;
}
