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

interface Tracker
{
    /**
     * Tracks an object.
     */
    public function track(object $entity): void;

    /**
     * Forgets an object.
     */
    public function forget(object $entity): void;

    /**
     * Returns true if an object is tracked.
     */
    public function isTracked(object $entity): bool;
}
