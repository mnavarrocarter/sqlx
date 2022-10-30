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

namespace MNC\SQLX\SQL;

use Castor\Context;

interface Mapper
{
    public const PHP_DATETIME_FORMAT = 'sqlx.php_datetime_format';
    public const DB_DATETIME_FORMAT = 'sqlx.db_datetime_format';

    /**
     * Converts a database value to a PHP value.
     *
     * @throws Mapper\ConversionError if the conversion fails
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed;

    /**
     * Converts a database value to a PHP value.
     *
     * @throws Mapper\ConversionError if the conversion fails
     */
    public function toDatabaseValue(Context $ctx, mixed $value): mixed;
}
