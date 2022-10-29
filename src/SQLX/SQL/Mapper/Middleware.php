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

namespace MNC\SQLX\SQL\Mapper;

use Castor\Context;
use MNC\SQLX\SQL\Mapper;

/**
 * Middleware is a mapper than can be used to compose a chain of mappers.
 */
abstract class Middleware implements Mapper
{
    private Mapper $next;

    public function __construct(Mapper $next)
    {
        $this->next = $next;
    }

    /**
     * {@inheritDoc}
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed
    {
        return $this->tryToPHPValue($ctx, $this->next, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function toDatabaseValue(Context $ctx, mixed $value): mixed
    {
        return $this->tryToDatabaseValue($ctx, $this->next, $value);
    }

    /**
     * Tries to map a database value to a PHP value.
     *
     * It defers to the next mapper otherwise
     *
     * @throws ConversionError
     */
    abstract protected function tryToPHPValue(Context $ctx, Mapper $next, mixed $value): mixed;

    /**
     * Tries to map a PHP value to a database value.
     *
     * It defers to the next mapper otherwise
     *
     * @throws ConversionError
     */
    abstract protected function tryToDatabaseValue(Context $ctx, Mapper $next, mixed $value): mixed;
}
