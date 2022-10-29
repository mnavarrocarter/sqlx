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

namespace MNC\SQLX\Engine\Tracker;

use MNC\SQLX\Engine\Tracker;
use WeakMap;

/**
 * The InMemory tracker tracks entities using a WeakMap.
 *
 * This allows the entities to be garbage collected if they are not
 * referenced anymore and PHP needs to free some memory.
 *
 * If an entity object goes out of scope is okay to remove it from the tracker,
 * as the object will need to be queried again by the application to be retrieved.
 */
final class InMemory implements Tracker
{
    private WeakMap $map;

    public function __construct()
    {
        $this->map = new WeakMap();
    }

    public function track(object $entity): void
    {
        $this->map->offsetSet($entity, time());
    }

    public function forget(object $entity): void
    {
        $this->map->offsetUnset($entity);
    }

    public function isTracked(object $entity): bool
    {
        return $this->map->offsetExists($entity);
    }
}
