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
    public function testInsertion(): void
    {
        $engine = Engine::configure($this->getConnection())
            ->withNamer(new Engine\Namer\Underscore())
            ->build()
        ;

        $ctx = Context\nil();

        $user = new User('Matias Navarro', 'mnavarrocarter@gmail.com', 'secret');

        $engine->persist($ctx, $user);

        $this->assertSame(1, $user->getId());
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

        $user = new User('Matias Navarro', 'mnavarrocarter@gmail.com', 'secret');

        $engine->persist($ctx, $user);
        $this->assertSame(1, $user->getId());

        $user->changePassword('secret2');
        $engine->persist($ctx, $user);
    }

    protected function getSetupStatements(): iterable
    {
        yield FromFile::open(__DIR__.'/testdata/sqlite/users.sql');
    }
}
