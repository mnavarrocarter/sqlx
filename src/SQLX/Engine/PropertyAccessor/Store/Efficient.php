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

namespace MNC\SQLX\Engine\PropertyAccessor\Store;

use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\PropertyAccessor\Store;

final class Efficient implements Store
{
    public ?PropertyAccessor\Efficient $instance = null;

    public function create(object $object): PropertyAccessor
    {
        if (null === $this->instance) {
            $this->instance = new PropertyAccessor\Efficient();
        }

        return $this->instance;
    }
}
