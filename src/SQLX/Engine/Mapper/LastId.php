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

namespace MNC\SQLX\Engine\Mapper;

use MNC\SQLX\SQL\Connection\Result;

class LastId
{
    private Result $result;

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    public function getValue(): string
    {
        return $this->result->getLastInsertedId();
    }
}
