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

use MNC\SQLX\Engine\PropertyAccessor\NonexistentProperty;

interface PropertyAccessor
{
    /**
     * Sets the properties in an object.
     *
     * @throws NonexistentProperty if the property does not exist
     */
    public function set(object $object, string $property, mixed $value): void;

    /**
     * Gets all the properties for an object.
     *
     * @throws NonexistentProperty if the property does not exist
     */
    public function get(object $object, string $property): mixed;

    /**
     * Checks whether a property exists in the object.
     */
    public function has(object $object, string $property): bool;
}
