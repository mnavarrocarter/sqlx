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

namespace MNC\SQLX\Engine\PropertyAccessor;

use MNC\SQLX\Entity;
use MNC\SQLX\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ClosureBasedTest extends TestCase
{
    public function testItCanAccessPrivateProps(): void
    {
        $object = new User(1, 'John Doe', 'jdoe@example.com', 'secret');

        $assessor = ClosureBased::make($object);
        $this->assertSame('John Doe', $assessor->get(User::class, 'name'));
    }

    public function testItCanAccessPrivatePropsFromParent(): void
    {
        $object = new User(1, 'John Doe', 'jdoe@example.com', 'secret');

        $assessor = ClosureBased::make($object);
        $this->assertInstanceOf(\DateTimeInterface::class, $assessor->get(Entity::class, 'createdAt'));
    }

    public function testItCanSetPrivateProps(): void
    {
        $object = new User(1, 'John Doe', 'jdoe@example.com', 'secret');

        $assessor = ClosureBased::make($object);
        $assessor->set(User::class, 'email', 'jdoe@domain.com');

        $this->assertSame('jdoe@domain.com', $object->getEmail());
    }

    public function testItCanSetPrivatePropsFromParent(): void
    {
        $object = new User(1, 'John Doe', 'jdoe@example.com', 'secret');
        $datetime = new \DateTimeImmutable();

        $assessor = ClosureBased::make($object);
        $assessor->set(Entity::class, 'createdAt', $datetime);

        $this->assertSame($datetime, $object->getCreatedAt());
    }
}
