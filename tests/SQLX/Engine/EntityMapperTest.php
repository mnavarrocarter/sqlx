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

namespace MNC\SQLX\Engine;

use MNC\SQLX\Engine\Mapper\FindClass;
use MNC\SQLX\Engine\Mapper\RawRecord;
use MNC\SQLX\Engine\Metadata\NotFound;
use MNC\SQLX\Engine\Metadata\Store\Reflection;
use MNC\SQLX\Engine\Namer\Underscore;
use MNC\SQLX\Engine\PropertyAccessor\Store\ClosureBased;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\User;
use PHPUnit\Framework\TestCase;
use Castor\Context;

/**
 * @internal
 *
 * @covers \MNC\SQLX\Engine\EntityMapper
 */
class EntityMapperTest extends TestCase
{
    /**
     * @return void
     * @throws Mapper\ConversionError
     */
    public function testItHandlesToPHPValueMetadataNotFound(): void
    {
        $next = $this->createMock(Mapper::class);
        $metadataStore = $this->createMock(Metadata\Store::class);
        $propertyAccessorFactory = $this->createMock(PropertyAccessor\Store::class);
        $connection = $this->createStub(Connection::class);
        $tracker = $this->createStub(Tracker::class);
        $mapper = new EntityMapper($next, $metadataStore, $propertyAccessorFactory);
        $ctx = Context\nil();
        $value = new FindClass($connection, $tracker, User::class);

        $metadataStore->expects($this->once())
            ->method('retrieve')
            ->with(User::class)
            ->willThrowException(new NotFound());

        $this->expectException(Mapper\ConversionError::class);

        $mapper->toPHPValue($ctx, $value);
    }

    /**
     * @return void
     * @throws Mapper\ConversionError
     */
    public function testItHandlesToDatabaseValueMetadataInvalid(): void
    {
        $next = $this->createMock(Mapper::class);
        $metadataStore = $this->createMock(Metadata\Store::class);
        $propertyAccessorFactory = $this->createMock(PropertyAccessor\Store::class);
        $mapper = new EntityMapper($next, $metadataStore, $propertyAccessorFactory);
        $ctx = Context\nil();
        $value = new \stdClass();

        $metadataStore->expects($this->once())
            ->method('retrieve')
            ->with(\stdClass::class)
            ->willThrowException(new Metadata\Invalid());

        $this->expectException(Mapper\ConversionError::class);

        $mapper->toDatabaseValue($ctx, $value);
    }

    /**
     * @return void
     * @throws Mapper\ConversionError
     */
    public function testItHandlesToDatabaseValueMetadataNotFound(): void
    {
        $next = $this->createMock(Mapper::class);
        $metadataStore = $this->createMock(Metadata\Store::class);
        $propertyAccessorFactory = $this->createMock(PropertyAccessor\Store::class);
        $mapper = new EntityMapper($next, $metadataStore, $propertyAccessorFactory);
        $ctx = Context\nil();
        $value = new \stdClass();

        $metadataStore->expects($this->once())
            ->method('retrieve')
            ->with(\stdClass::class)
            ->willThrowException(new NotFound());

        $next->expects($this->once())
            ->method('toDatabaseValue')
            ->with($ctx, $value)
            ->willReturn($value);

        $returned = $mapper->toDatabaseValue($ctx, $value);
        $this->assertSame($value, $returned);
    }

    /**
     * @return void
     * @throws Mapper\ConversionError
     */
    public function testItBuildsRawRecord(): void
    {
        $next = new Mapper\Standard();
        $metadataStore = new Reflection(new Underscore());
        $propertyAccessorFactory = new ClosureBased();
        $mapper = new EntityMapper($next, $metadataStore, $propertyAccessorFactory);
        $ctx = Context\nil();

        $value = new User(1, 'John Doe', 'jdoe@example.com', 'secret');

        $record = $mapper->toDatabaseValue($ctx, $value);

        $this->assertInstanceOf(RawRecord::class, $record);
        $this->assertSame('user', $record->table);
        $this->assertArrayHasKey('id', $record->data);
        $this->assertArrayHasKey('tenant_id', $record->data);
        $this->assertArrayHasKey('name', $record->data);
        $this->assertArrayHasKey('email', $record->data);
        $this->assertArrayHasKey('password', $record->data);
        $this->assertArrayHasKey('created_at', $record->data);
    }
}
