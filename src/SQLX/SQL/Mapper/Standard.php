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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use MNC\SQLX\SQL\Mapper;

/**
 * Standard is a mapper that attempts to map the most basic types into
 * database values for known drivers and versions.
 */
final class Standard implements Mapper
{
    /**
     * {@inheritDoc}
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        $type = $ctx->value(Mapper::CTX_PHP_TYPE);

        return match ($type) {
            null => $value,
            'int', 'integer' => (int) ($value ?? 0),
            'string' => (string) ($value ?? ''),
            'float' => (float) ($value ?? 0.0),
            'bool' => true === $value || 1 === $value || 'true' === $value,
            DateTimeInterface::class,
            DateTimeImmutable::class => DateTimeImmutable::createFromFormat(DATE_ATOM, $value),
            DateTime::class => DateTime::createFromFormat(DATE_ATOM, $value),
            default => throw new ConversionError(sprintf(
                'Cannot map database value to %s type',
                $type
            ))
        };
    }

    /**
     * {@inheritDoc}
     */
    public function toDatabaseValue(Context $ctx, mixed $value): mixed
    {
        if (is_string($value) || is_int($value) || is_bool($value) || is_null($value) || is_float($value)) {
            return $value;
        }

        // Only objects from here
        if (!is_object($value)) {
            throw new ConversionError(sprintf(
                'Cannot map object of class %s to database value',
                get_class($value)
            ));
        }

        // This is the default format for dates
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        throw new ConversionError(sprintf(
            'Cannot map object of class %s to database value',
            get_class($value)
        ));
    }
}
