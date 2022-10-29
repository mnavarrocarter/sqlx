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

namespace MNC\SQLX\Engine\PropertyAccessor;

use MNC\SQLX\Engine\PropertyAccessor;
use RuntimeException;

/**
 * This property accessor implements the most performant technique to extract
 * properties described in this blog post.
 *
 * @see https://www.lambda-out-loud.com/posts/accessing-private-properties-php/
 */
final class Efficient implements PropertyAccessor
{
    /**
     * {@inheritDoc}
     */
    public function set(object $object, string $property, mixed $value): void
    {
        $this->ensureProperty($object, $property);

        array_walk($object, static function (&$inner, $key) use ($property, $value) {
            if (str_ends_with($key, $property)) {
                $inner = $value;
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function get(object $object, string $property): mixed
    {
        $this->ensureProperty($object, $property);

        $array = (array) $object;
        $propertyLength = strlen($property);
        foreach ($array as $key => $value) {
            if (substr($key, -$propertyLength) === $property) {
                return $value;
            }
        }

        // This should never happen
        throw new RuntimeException(sprintf(
            'Invalid property_exists report for property "%d" in class "%s"',
            $property,
            get_class($object)
        ));
    }

    public function has(object $object, string $property): bool
    {
        return property_exists($object, $property);
    }

    /**
     * @throws NonexistentProperty
     */
    private function ensureProperty(object $object, string $property): void
    {
        if (!$this->has($object, $property)) {
            throw new NonexistentProperty(sprintf('No property "%d" in class "%s"', $property, get_class($object)));
        }
    }
}
