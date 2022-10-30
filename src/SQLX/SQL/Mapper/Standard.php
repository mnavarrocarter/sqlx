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
use MNC\SQLX\SQL\Driver;
use MNC\SQLX\SQL\Mapper;

/**
 * Standard is a mapper that attempts to map the most basic types and is aware
 * as much as possible between the different driver differences.
 */
final class Standard implements Mapper
{
    private const PGSQL_TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    /**
     * {@inheritDoc}
     */
    public function toPHPValue(Context $ctx, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        $type = getPHPType($ctx);

        return match ($type) {
            '' => $value,
            'int', 'integer' => (int) ($value ?? 0),
            'string' => (string) ($value ?? ''),
            'float' => (float) ($value ?? 0.0),
            'bool' => true === $value || 1 === $value || 'true' === $value,
            DateTimeInterface::class,
            DateTimeImmutable::class => $this->toPHPDatetime($ctx, $value, true),
            DateTime::class => $this->toPHPDatetime($ctx, $value),
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
        // PDO can handle all this
        if (is_string($value) || is_int($value) || is_bool($value) || is_null($value) || is_float($value)) {
            return $value;
        }

        // Only objects from here
        if (!is_object($value)) {
            throw new ConversionError(sprintf(
                'Cannot value of type %s to database value',
                gettype($value)
            ));
        }

        if ($value instanceof DateTimeInterface) {
            return $this->toDatabaseDatetime($ctx, $value);
        }

        throw new ConversionError(sprintf(
            'Cannot map object of class %s to database value',
            get_class($value)
        ));
    }

    /**
     * @throws ConversionError
     */
    private function toPHPDatetime(Context $ctx, mixed $value, bool $immutable = false): DateTimeInterface
    {
        $driver = Driver\getDriver($ctx);

        $format = match ($driver->getName()) {
            Driver::MYSQL, Driver::PGSQL => self::PGSQL_TIMESTAMP_FORMAT,
            default => DATE_ATOM,
        };

        $format = getMetadata($ctx)[Mapper::PHP_DATETIME_FORMAT] ?? $format;

        $time = $immutable ?
            DateTimeImmutable::createFromFormat($format, $value) :
            DateTime::createFromFormat($format, $value);

        if (!$time instanceof DateTimeInterface) {
            throw new ConversionError(sprintf(
                'Could not map date value "%s" with format "%s" for driver "%s"',
                $value,
                $format,
                $driver->getName()
            ));
        }

        return $time;
    }

    private function toDatabaseDatetime(Context $ctx, DateTimeInterface $value): string
    {
        $driver = Driver\getDriver($ctx);

        $format = match ($driver->getName()) {
            Driver::MYSQL, Driver::PGSQL => self::PGSQL_TIMESTAMP_FORMAT,
            default => DATE_ATOM,
        };

        $format = getMetadata($ctx)[Mapper::DB_DATETIME_FORMAT] ?? $format;

        return $value->format($format);
    }
}
