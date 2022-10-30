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
use MNC\SQLX\Engine\Finder\Filterable;
use MNC\SQLX\Engine\Metadata;

/**
 * @internal
 */
const FILTER_KEY = 'sqlx.filters';

/**
 * @param callable(Filterable, Metadata):Filter|void $filter
 */
function withFilter(Context $ctx, Filter|callable $filter): Context
{
    if (is_callable($filter)) {
        $filter = ClosureFilter::fromCallable($filter);
    }

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
