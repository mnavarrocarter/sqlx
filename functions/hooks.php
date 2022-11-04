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

use Castor\Context;

/**
 * @internal
 */
const FILTER_KEY = 'sqlx.filters';

function withFilterFn(Context $ctx, callable $filter): Context
{
    return withFilter($ctx, ClosureFilter::fromCallable($filter));
}

function withFilter(Context $ctx, Filter $filter): Context
{
    $collection = $ctx->value(FILTER_KEY);
    if ($collection instanceof Collection) {
        $collection->addFilter($filter);

        return $ctx;
    }

    $collection = new Collection($filter);

    return Context\withValue($ctx, FILTER_KEY, $collection);
}

function getFilters(Context $ctx): Filter
{
    return $ctx->value(FILTER_KEY) ?? new Collection();
}

/**
 * @internal
 */
const ARRAY_HYDRATE_KEY = 'sqlx.array_hydration';

function withArrayHydration(Context $ctx, string ...$exclude): Context
{
    return Context\withValue($ctx, ARRAY_HYDRATE_KEY, $exclude);
}

/**
 * @return null|string[]
 */
function getArrayHydration(Context $ctx): ?array
{
    return $ctx->value(ARRAY_HYDRATE_KEY);
}
