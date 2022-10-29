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

/**
 * A Namer implements algorithms to convert class names to table names and
 * column names to property names, and vice-versa.
 */
interface Namer
{
    /**
     * Converts a fully qualified class name into a table name.
     */
    public function classToTable(string $class): string;

    /**
     * Converts a property name into a column name.
     */
    public function propertyToColumn(string $class, string $property): string;
}
