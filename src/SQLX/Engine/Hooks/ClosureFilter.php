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

namespace MNC\SQLX\Engine\Hooks;

use Closure;
use MNC\SQLX\Engine\Finder\Filterable;
use MNC\SQLX\Engine\Metadata;

final class ClosureFilter implements Filter
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public static function fromCallable(callable $callable): ClosureFilter
    {
        return new self(Closure::fromCallable($callable));
    }

    public function apply(Filterable $query, Metadata $metadata): void
    {
        ($this->closure)($query, $metadata);
    }
}
