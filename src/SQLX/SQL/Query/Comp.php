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

/**
 * Comp represents a comparison statement.
 *
 * It usually involves a column and an operator, along with a set of values.
 */
final class Comp implements Clause
{
    private const EQ = '=';
    private const NEQ = '!=';
    private const GT = '>';
    private const GTE = '>=';
    private const LT = '<';
    private const LTE = '<=';
    private const NULL = 'IS NULL';
    private const NOT_NULL = 'NOT NULL';
    private const BETWEEN = 'BETWEEN';
    private const IN = 'IN';
    private const LIKE = 'LIKE';

    public string $column;
    public string $operator;
    public array $params;

    private function __construct(string $operator, string $column, mixed ...$params)
    {
        $this->operator = $operator;
        $this->column = $column;
        $this->params = $params;
    }

    public static function eq(string $column, mixed $value): Comp
    {
        return new Comp(self::EQ, $column, $value);
    }

    public static function neq(string $column, mixed $value): Comp
    {
        return new Comp(self::NEQ, $column, $value);
    }

    public static function gt(string $column, mixed $value): Comp
    {
        return new Comp(self::GT, $column, $value);
    }

    public static function gte(string $column, mixed $value): Comp
    {
        return new Comp(self::GTE, $column, $value);
    }

    public static function lt(string $column, mixed $value): Comp
    {
        return new Comp(self::LT, $column, $value);
    }

    public static function lte(string $column, mixed $value): Comp
    {
        return new Comp(self::LTE, $column, $value);
    }

    public static function null(string $column): Comp
    {
        return new Comp(self::NULL, $column);
    }

    public static function notNull(string $column): Comp
    {
        return new Comp(self::NOT_NULL, $column);
    }

    public static function between(string $column, mixed $a, mixed $b): Comp
    {
        return new Comp(self::BETWEEN, $column, $a, $b);
    }

    public static function in(string $column, mixed ...$values): Comp
    {
        return new Comp(self::IN, $column, ...$values);
    }

    public static function like(string $column, string $value): Comp
    {
        return new Comp(self::LIKE, $column, $value);
    }

    public static function custom(string $column, string $operator, mixed $value): Comp
    {
        return new Comp($column, $operator, $value);
    }

    public function getSQL(Dialect $dialect): string
    {
        return match ($this->operator) {
            self::EQ,
            self::NEQ,
            self::GT,
            self::GTE,
            self::LT,
            self::LTE => sprintf(
                '%s %s ?',
                $dialect->quoteColumn($this->column),
                $this->operator
            ),

            self::NULL,
            self::NOT_NULL => sprintf(
                '%s %s',
                $dialect->quoteColumn($this->column),
                $this->operator
            ),

            self::BETWEEN => sprintf(
                '%s %s ? AND ?',
                $dialect->quoteColumn($this->column),
                $this->operator
            ),

            self::IN => sprintf(
                '%s %s (%s)',
                $dialect->quoteColumn($this->column),
                $this->operator,
                implode(', ', array_fill(0, count($this->params), '?'))
            ),

            // This includes the LIKE clause
            default => sprintf(
                '%s %s ?',
                $dialect->quoteColumn($this->column),
                $this->operator,
            ),
        };
    }

    public function getParameters(Dialect $dialect): array
    {
        return array_map([$dialect, 'cleanValue'], $this->params);
    }
}
