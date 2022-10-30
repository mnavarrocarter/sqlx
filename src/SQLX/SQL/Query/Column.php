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

namespace MNC\SQLX\SQL\Query;

use MNC\SQLX\SQL\Dialect;

final class Column implements Clause
{
    private string $name;
    private string $as;

    public function __construct(string $name, string $as)
    {
        $this->name = $name;
        $this->as = $as;
    }

    public function getSQL(Dialect $dialect): string
    {
        $col = $dialect->quoteColumn($this->name);
        if ('' !== $this->as) {
            $col .= ' AS '.$this->as;
        }

        return $col;
    }

    public function getParameters(Dialect $dialect): array
    {
        return [];
    }
}
