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

namespace MNC\SQLX\Engine\Namer;

use MNC\SQLX\Engine\Namer;

final class Underscore implements Namer
{
    use ClassStripper;

    public function classToTable(string $class): string
    {
        return $this->pascalToSnake($this->stripClass($class));
    }

    public function propertyToColumn(string $class, string $property): string
    {
        return $this->pascalToSnake($property);
    }

    private function pascalToSnake(string $pascal): string
    {
        return strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $pascal), ' _'));
    }
}
