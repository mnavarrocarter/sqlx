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

namespace MNC\SQLX\Engine\Finder;

use Castor\Context;

/**
 * @internal
 */
const HYDRATE_KEY = 'sqlx.hydration';

/**
 * @internal
 */
const HYDRATION_ARRAY = 0;

/**
 * @internal
 */
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
