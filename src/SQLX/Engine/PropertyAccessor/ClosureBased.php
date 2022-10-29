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
use WeakReference;

/**
 * This ClosureBased property accessor binds closures to a particular scope and
 * performs the requested operation.
 *
 * It's more efficient than the approach explained in the blog-post bellow, because
 * it avoids binding closures for every property call. Instead, it binds once
 * per scope and the object is passed as an argument.
 *
 * Since the closures are static, there are also a bit more efficient.
 *
 * The object is stored in a weak ref, so it can be replaced, which avoids
 * regenerating all the cached scopes.
 *
 * @see https://www.lambda-out-loud.com/posts/accessing-private-properties-php/
 */
final class ClosureBased implements PropertyAccessor
{
    private WeakReference $object;

    /**
     * @var array<string,callable(object, string):mixed>
     */
    private array $getters = [];

    /**
     * @var array<string,callable(object, string, mixed):void>
     */
    private array $setters = [];

    /**
     * @var array<string,callable(object, string):bool>
     */
    private array $hassers = [];

    public function __construct(WeakReference $object)
    {
        $this->object = $object;
    }

    public static function make(object $object): ClosureBased
    {
        return new self(WeakReference::create($object));
    }

    public function changeRef(object $object): void
    {
        $this->object = WeakReference::create($object);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $scope, string $property, mixed $value): void
    {
        $this->ensureProperty($scope, $property);
        $this->getSetter($scope)($this->object->get(), $property, $value);
    }

    /**
     * @return callable(object, string, mixed):void
     */
    public function getSetter(string $scope): callable
    {
        $setter = $this->setters[$scope] ?? null;
        if (null === $setter) {
            $setter = \Closure::bind(static function (object $object, string $prop, mixed $value): void {
                $object->{$prop} = $value;
            }, null, $scope);
            $this->setters[$scope] = $setter;
        }

        return $setter;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $scope, string $property): mixed
    {
        $this->ensureProperty($scope, $property);

        return $this->getGetter($scope)($this->object->get(), $property);
    }

    public function has(string $scope, string $property): bool
    {
        return $this->getHasser($scope)($this->object->get(), $property);
    }

    /**
     * @return callable(object, string): bool
     */
    private function getHasser(string $scope): callable
    {
        $hasser = $this->hassers[$scope] ?? null;
        if (null === $hasser) {
            $hasser = \Closure::bind(static function (object $object, string $prop): bool {
                return property_exists($object, $prop);
            }, null, $scope);
            $this->hassers[$scope] = $hasser;
        }

        return $hasser;
    }

    /**
     * @throws NonexistentProperty
     */
    private function ensureProperty(string $scope, string $property): void
    {
        if (!$this->has($scope, $property)) {
            throw new NonexistentProperty(sprintf('No property "%d" in scope "%s"', $property, $scope));
        }
    }

    /**
     * @return callable(object, string): mixed
     */
    private function getGetter(string $scope): callable
    {
        $getter = $this->getters[$scope] ?? null;
        if (null === $getter) {
            $getter = \Closure::bind(static function (object $object, string $prop): mixed {
                return $object->{$prop};
            }, null, $scope);
            $this->getters[$scope] = $getter;
        }

        return $getter;
    }
}
