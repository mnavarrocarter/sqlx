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
use MNC\SQLX\Engine\Finder\FinderError;
use MNC\SQLX\Engine\Finder\MoreThanOneError;
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
    public const FILENAME = __DIR__.'/testdata/database.sqlite';

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
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(3, $user->getId());

        $this->assertRecordContains('user', 'id', 3, [
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
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $user = new User("Bernardo O'Higgins", 'bohigg@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(3, $user->getId());

        $this->assertRecordContains('user', 'id', 3, [
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
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $user = new User('John Doe', 'jdoe@example.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(3, $user->getId());
        $this->assertRecordContains('user', 'id', 3, [
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'password' => 'secret',
        ]);

        $user->changePassword('secret2');
        $engine->persist($ctx, $user);

        $this->assertRecordContains('user', 'id', 3, [
            'password' => 'secret2',
        ]);
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws NotFoundError
     */
    public function testFindWithClause(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = $engine
            ->find($ctx, User::class)
            ->andWhere(Comp::notNull('createdAt'))
            ->first()
        ;

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->getId());
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws MoreThanOneError
     * @throws NotFoundError
     */
    public function testFindAndUpdate(): void
    {
        $engine = $this->getEngine();

        $ctx = Context\nil();

        /** @var User $user */
        $user = $engine
            ->find($ctx, User::class)
            ->andWhere('id = ?', 2)
            ->one()
        ;

        $user->changePassword('secret2');

        $engine->persist($ctx, $user);

        $this->assertRecordContains('user', 'id', 2, [
            'password' => 'secret2',
        ]);
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws MoreThanOneError
     * @throws NotFoundError
     */
    public function testFindAndDelete(): void
    {
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $user = $engine
            ->find($ctx, User::class)
            ->andWhere('id = ?', 2)
            ->one()
        ;

        $engine->delete($ctx, $user);

        $this->assertRecordNotExists('user', 'id', 2);
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws ScanError
     */
    public function testFindsNoneFoundWithRowsArray(): void
    {
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $data = $engine->find($ctx, User::class)
            ->andWhere(Comp::eq('name', 'Peter Quinn'))
            ->slice(0, 10)->rows()->toArray();

        $this->assertCount(0, $data);
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws NotFoundError
     */
    public function testFindsMoreThanOne(): void
    {
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $this->expectException(MoreThanOneError::class);
        $engine->find($ctx, User::class)->one();
    }

    /**
     * @throws EngineError
     * @throws FinderError
     * @throws ScanError
     */
    public function testFindsAll(): void
    {
        $engine = $this->getEngine();

        $ctx = Context\nil();

        $users = $engine->find($ctx, User::class)->rows()->toArray();

        $this->assertCount(2, $users);
    }

    protected function getEngine(): Engine
    {
        return Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;
    }

    protected function getSetupStatements(): iterable
    {
        yield FromFile::open(__DIR__.'/testdata/sqlite.schema.sql');

        yield FromFile::open(__DIR__.'/testdata/sqlite.seed.sql');
    }
}
