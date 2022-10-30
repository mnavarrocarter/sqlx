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

/**
 * @internal
 */
const PHP_TYPE = 'sqlx.php_type';

/**
 * @internal
 */
const DB_TABLE = 'sqlx.db_table';

/**
 * @internal
 */
const DB_COLUMN = 'sqlx.db_column';

/**
 * @internal
 */
const METADATA = 'sqlx.metadata';

function withPHPType(Context $ctx, string $type): Context
{
    return Context\withValue($ctx, PHP_TYPE, $type);
}

function getPHPType(Context $ctx): string
{
    return $ctx->value(PHP_TYPE) ?? '';
}

function withMetadata(Context $ctx, array $meta): Context
{
    return Context\withValue($ctx, METADATA, $meta);
}

function getMetadata(Context $ctx): array
{
    return $ctx->value(METADATA) ?? [];
}

function withTableName(Context $ctx, string $table): Context
{
    return Context\withValue($ctx, DB_TABLE, $table);
}

function getTableName(Context $ctx): string
{
    return $ctx->value(DB_TABLE) ?? '';
}

function withColumnName(Context $ctx, string $column): Context
{
    return Context\withValue($ctx, DB_COLUMN, $column);
}

function getColumnName(Context $ctx): string
{
    return $ctx->value(DB_COLUMN) ?? '';
}
