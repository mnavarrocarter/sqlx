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

namespace MNC\SQLX\Engine\Metadata;

use MNC\SQLX\Engine\Metadata;

interface Store
{
    /**
     * Creates the metadata for an object.
     *
     * @throws NotFound if there is no metadata for the class
     * @throws Invalid  if the metadata is invalid
     */
    public function retrieve(string $class): Metadata;
}
