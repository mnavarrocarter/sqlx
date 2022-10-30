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

namespace MNC\SQLX;

use Castor\Context;
use MNC\SQLX\Engine\Mapper\FindClass;
use MNC\SQLX\Engine\Mapper\MapLastId;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Mapper;
use MNC\SQLX\SQL\Statement;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MNC\SQLX\Engine
 */
class EngineTest extends TestCase
{
    public function testItDoesNotFindEntity(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Error while creating finder for class MNC\SQLX\Entity.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();

        $mapper->expects($this->once())
            ->method('toPHPValue')
            ->with($ctx, $this->isInstanceOf(FindClass::class))
            ->willThrowException(new Mapper\ConversionError())
        ;

        $engine->find($ctx, Entity::class);
    }

    public function testItHandlesMapperReturningWrongObject(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Returned mapped value is not an instance of MNC\SQLX\Engine\Finder.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();

        $mapper->expects($this->once())
            ->method('toPHPValue')
            ->with($ctx, $this->isInstanceOf(FindClass::class))
            ->willReturn(new \stdClass())
        ;

        $engine->find($ctx, Entity::class);
    }

    public function testItHandlesMappingErrorOnPersist(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Error while trying to build SQL statement for class stdClass.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(false)
        ;

        $mapper->expects($this->once())
            ->method('toDatabaseValue')
            ->with($this->isInstanceOf(Context\KVPair::class), $object)
            ->willThrowException(new Mapper\ConversionError())
        ;

        $engine->persist($ctx, new \stdClass());
    }

    public function testItHandlesNoStatementOnPersist(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Returned mapped value is not an instance of MNC\SQLX\SQL\Statement.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(false)
        ;

        $mapper->expects($this->once())
            ->method('toDatabaseValue')
            ->with($this->isInstanceOf(Context\KVPair::class), $object)
            ->willReturn(new \stdClass())
        ;

        $engine->persist($ctx, new \stdClass());
    }

    public function testItHandlesPersistError(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('An error occurred while executing the query.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();
        $statement = $this->createStub(Statement::class);

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(false)
        ;

        $mapper->expects($this->once())
            ->method('toDatabaseValue')
            ->with($this->isInstanceOf(Context\KVPair::class), $object)
            ->willReturn($statement)
        ;

        $connection->expects($this->once())
            ->method('execute')
            ->with($ctx, $statement)
            ->willThrowException(new Connection\ExecutionError())
        ;

        $engine->persist($ctx, new \stdClass());
    }

    public function testItHandlesIdUpdateError(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Unexpected error trying to map the last inserted id.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();
        $statement = $this->createStub(Statement::class);
        $result = $this->createStub(Connection\Result::class);

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(false)
        ;

        $mapper->expects($this->once())
            ->method('toDatabaseValue')
            ->with($this->isInstanceOf(Context\KVPair::class), $object)
            ->willReturn($statement)
        ;

        $connection->expects($this->once())
            ->method('execute')
            ->with($ctx, $statement)
            ->willReturn($result)
        ;

        $mapper->expects($this->once())
            ->method('toPHPValue')
            ->with($ctx, $this->isInstanceOf(MapLastId::class))
            ->willThrowException(new Mapper\ConversionError())
        ;

        $engine->persist($ctx, new \stdClass());
    }

    public function testItHandlesNonTrackedDelete(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('Cannot delete an untracked object.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(false)
        ;

        $engine->delete($ctx, new \stdClass());
    }

    public function testItHandlesDeleteError(): void
    {
        $this->expectException(EngineError::class);
        $this->expectErrorMessage('An error occurred while executing the query.');

        $connection = $this->createMock(Connection::class);
        $tracker = $this->createMock(Tracker::class);
        $mapper = $this->createMock(Mapper::class);
        $engine = new Engine($connection, $tracker, $mapper);
        $ctx = Context\nil();
        $object = new \stdClass();
        $statement = $this->createStub(Statement::class);

        $tracker->expects($this->once())
            ->method('isTracked')
            ->with($object)
            ->willReturn(true)
        ;

        $mapper->expects($this->once())
            ->method('toDatabaseValue')
            ->with($this->isInstanceOf(Context\KVPair::class), $object)
            ->willReturn($statement)
        ;

        $connection->expects($this->once())
            ->method('execute')
            ->with($ctx, $statement)
            ->willThrowException(new Connection\ExecutionError())
        ;

        $engine->delete($ctx, new \stdClass());
    }
}
