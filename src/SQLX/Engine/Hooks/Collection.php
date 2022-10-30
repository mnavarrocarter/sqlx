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

use MNC\SQLX\Engine\Finder\Filterable;
use MNC\SQLX\Engine\Metadata;

final class Collection implements Filter
{
    /**
     * @var Filter[]
     */
    private array $filters;

    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    public function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    public function apply(Filterable $query, Metadata $metadata): void
    {
        foreach ($this->filters as $filter) {
            $filter->apply($query, $metadata);
        }
    }
}
