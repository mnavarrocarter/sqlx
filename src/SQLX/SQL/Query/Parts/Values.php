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

namespace MNC\SQLX\SQL\Query\Parts;

use MNC\SQLX\SQL\Dialect;
use MNC\SQLX\SQL\Query\InvalidQuery;

trait Values
{
    private array $columns = [];

    /**
     * @var array[]
     */
    private array $values = [];

    /**
     * @throws InvalidQuery
     */
    private function addValues(array $values): void
    {
        if ([] === $this->columns) {
            $this->columns = array_keys($values);
        }

        if (count($values) !== count($this->columns)) {
            throw new InvalidQuery('Number of values do not match the number of columns');
        }

        $this->values[] = array_values($values);
    }

    /**
     * @param array<string,mixed> $set
     */
    private function addSet(array $set): void
    {
        $this->columns = array_keys($set);
        $this->values = [array_values($set)];
    }

    private function getSQLForValues(Dialect $driver): string
    {
        $cols = implode(', ', array_map([$driver, 'quoteColumn'], $this->columns));

        $values = [];
        foreach ($this->values as $params) {
            $values[] = '('.implode(', ', array_fill(0, count($params), '?')).')';
        }

        return sprintf('(%s) VALUES %s', $cols, implode(', ', $values));
    }

    private function getValueParameters(Dialect $driver): array
    {
        return array_map([$driver, 'cleanValue'], array_merge(...$this->values));
    }

    private function getSQLForSet(Dialect $driver): string
    {
        if ([] === $this->values) {
            return '';
        }

        $sets = [];

        foreach ($this->columns as $column) {
            $sets[] = $driver->quoteColumn($column).' = ?';
        }

        return sprintf('SET %s', implode(', ', $sets));
    }
}
