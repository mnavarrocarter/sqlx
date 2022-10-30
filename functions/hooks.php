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
const HYDRATE_KEY = 'sqlx.hydration';

const HYDRATION_ARRAY = 0;

const HYDRATION_OBJECT = 1;

function withArrayHydration(Context $ctx): Context
{
    return Context\withValue($ctx, HYDRATE_KEY, HYDRATION_ARRAY);
}

function withObjectHydration(Context $ctx): Context
{
    return Context\withValue($ctx, HYDRATE_KEY, HYDRATION_OBJECT);
}

function getHydrationMode(Context $ctx): int
{
    return $ctx->value(HYDRATE_KEY) ?? HYDRATION_OBJECT;
}
