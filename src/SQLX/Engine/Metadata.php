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

use MNC\SQLX\Engine\Metadata\Field;

interface Metadata
{
    public function getTableName(): string;

    public function getClassName(): string;

    /**
     * @return Field[]
     */
    public function getFields(): array;

    public function getFieldByProp(string $name): ?Field;

    public function getFieldByColumn(string $name): ?Field;

    public function newInstance(): object;
}
