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

namespace MNC\SQLX\SQL\Connection;

use Traversable;

interface Rows extends Traversable
{
    /**
     * @throws ScanError
     */
    public function scanAssoc(array &$value): void;

    /**
     * @param array $values
     *
     * @throws ScanError
     */
    public function scan(mixed &...$values): void;

    /**
     * @throws ScanError
     */
    public function toArray(): array;
}
