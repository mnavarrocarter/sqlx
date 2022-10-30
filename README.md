SQLX
====

Simple, low-overhead relational database toolkit for modern PHP applications.

![php-workflow](https://github.com/mnavarrocarter/sqlx/actions/workflows/php.yml/badge.svg?branch=main)
![code-coverage](https://img.shields.io/badge/Coverage-71%25-yellow.svg?longCache=true&style=flat)

## Quick Start

You can map any of your classes as an entity just marking with the `SQLX\Entity` attribute and 
marking the id fields with the `SQLX\Id` attributes. All the other stuff can be guessed at runtime.

Of course, you can also be explicit by passing the `SQLX\Field` annotation.

```php
<?php

use MNC\SQLX\Engine\Metadata as SQLX;

#[SQLX\Entity]
class Account
{
    #[SQLX\Id]
    private int $id;
    #[SQLX\Field('user_name')]
    private string $username;
    private string $email;
    private string $password;
    private \DateTimeImmutable $createdAt;
    
    public function __construct(string $username, string $email, string $password)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = new DateTimeImmutable();    
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function changePassword(string $password): void
    {
        $this->password = $newPassword;
    }
}
```

Then, in your bootstrapping code, you can initialize the engine. Here we configure it with a simple
`PDOWrapper` connection instance. We also add a `Namer` strategy to cast all properties without 
explicit column names to underscore. So `createdAt` property will be mapped to the `created_at` 
column.

```php
<?php

use MNC\SQLX\SQL\Connection\PDOWrapper;
use MNC\SQLX\Engine;
use Castor\Context;

$conn = PDOWrapper::from(new PDO('sqlite::memory'));
$engine = Engine::configure($conn)
    ->withNamer(new Engine\Namer\Underscore())
    ->build()
;
```

Once you have the engine bootstrapped, is easy to inject it into your services and use its public 
api. This is the full public api at the moment. Every other detail is considered internal.

```php
$ctx = Context\nil();

// You can work with your objects in the domain layer.
$account = new Account('jdoe', 'jdoe@example.com', 'secret');

// Persisting a new object causes an insert:
// INSERT INTO account (user_name, email, password, created_at) VALUES ('jdoe', 'jdoe@example.com', 'secret')
$engine->persist($ctx, $account);

// Upon insertion, we can fetch the last inserted id.
// We grab it automatically for you if the driver supports it.
echo $user->getId(); // (int) 1 

// Is easy to find records:
// SELECT FROM account * WHERE id = 1;
$account = $engine->find($ctx, Account::class)->andWhere('id = ?', 1)->one();

$account->changePassword('secret2');

// Persisting an existing or "known" object causes an update:
// UPDATE account SET user_name = 'jdoe', email = 'jdoe@example.com', password = 'secret2' WHERE id = 1
$engine->persist($ctx, $account);

// You can delete an object of course
// DELETE FROM account WHERE id = 1
$engine->delete($ctx, $account);
```

> NOTE: All queries are correctly escaped and parametrized.

## Caveats

### This is not an ORM

This is not an Object Relational Mapper, because it does not do relationships. They are unsupported
and not planned for the moment. It would say is an Object Mapper: maps objects from the records in
your database, but even that is far-fetched. I prefer the term "database toolkit".

Proper relationship support is one of the biggest factors in making an ORM complex. Tracking
lifecycle of nested objects, detecting their changes and other things related to relationships can 
incur in a tremendous performance penalty. Moreover, relations are overrated: there are not needed 
in the majority of cases and often bite inexperienced developers with all sorts of bugs (N+1 
and bi-directional associations).

Even [Doctrine best practices][doctrine-bp] hint that unnecessary relationships should be avoided,
and lists a few other topics where the constraints imposed by relationships can affect performance.

Therefore, I'm avoiding full relationship support at the moment.

[doctrine-bp]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/reference/best-practices.html

### Needs more testing

Although the codebase is fairly tested and critical routines are well covered, I still need 
write more test cases in different drivers, with different queries and edge cases.

Building up a mature test suite like that takes time, but if you are interested in improving 
support for a particular Driver or Engine, I would happily take a PR. The `FunctionalTestCase` has
all you need to set up a connection and start testing against a particular engine. I'm interested
mostly in how the database receives certain data types (dates, blobs), handling of reserved keywords, 
identifier quoting and other things. And, of course, the main api of find, persist and delete needs
to be working too.