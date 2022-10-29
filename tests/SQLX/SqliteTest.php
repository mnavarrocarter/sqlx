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
use DateTimeImmutable;
use MNC\SQLX\Engine\Finder\FinderError;
use MNC\SQLX\Engine\Finder\NotFoundError;
use MNC\SQLX\SQL\Connection\ScanError;
use MNC\SQLX\SQL\Query\Comp;
use MNC\SQLX\SQL\Query\FromFile;

/**
 * An acceptance test is a test that runs the main public api of this library
 * and ensures functionality is not broken.
 *
 * @internal
 *
 * @coversDefaultClass
 */
class SqliteTest extends FunctionalTestCase
{
    public const FILENAME = __DIR__.'/testdata/sqlite/db.sqlite';

    protected array $params = ['sqlite://'.self::FILENAME];

    public function setUp(): void
    {
        if (is_file(self::FILENAME)) {
            unlink(self::FILENAME);
        }
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unlink(self::FILENAME);
    }

    /**
     * @throws EngineError
     */
    public function testItInsertsNormally(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(1, $user->getId());

        $this->assertRecordContains('user', 'id', 1, [
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'password' => 'secret',
        ]);
    }

    /**
     * @throws EngineError
     */
    public function testItInsertsStringWithQuote(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User("Bernardo O'Higgins", 'bohigg@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(1, $user->getId());

        $this->assertRecordContains('user', 'id', 1, [
            'name' => "Bernardo O'Higgins",
            'email' => 'bohigg@example.com',
            'password' => 'secret',
        ]);
    }

    /**
     * @throws EngineError
     */
    public function testInsertionAndImmediateUpdate(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(1, $user->getId());
        $this->assertRecordContains('user', 'id', 1, [
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'password' => 'secret',
        ]);

        $user->changePassword('secret2');
        $engine->persist($ctx, $user);

        $this->assertRecordContains('user', 'id', 1, [
            'password' => 'secret2',
        ]);
    }

    /**
     * @throws EngineError
     */
    public function testInsertAndDelete(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertRecordExists('user', 'id', 1);

        $engine->delete($ctx, $user);

        $this->assertRecordNotExists('user', 'id', 1);
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws NotFoundError
     */
    public function testInsertAndFindWithRawClause(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $user = $engine->find($ctx, User::class)->andWhere('id = ?', 1)->first();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertSame(1, $user->getId());
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws NotFoundError
     */
    public function testInsertAndFindWithClause(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $user = $engine
            ->find($ctx, User::class)
            ->andWhere(Comp::notNull('createdAt'))
            ->first()
        ;

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertSame(1, $user->getId());
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws ScanError
     */
    public function testFindsOneNotFound(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $data = $engine->find($ctx, User::class)
            ->andWhere(Comp::lt('createdAt', new DateTimeImmutable()))
            ->slice(0, 10)->rows()->toArray();

        $this->assertCount(0, $data);
    }

    protected function getSetupStatements(): iterable
    {
        yield FromFile::open(__DIR__.'/testdata/sqlite/users.sql');
    }
}
