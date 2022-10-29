SQLX
====

Modern, low-overhead relational database toolkit for simple PHP applications.

![php-workflow](https://github.com/mnavarrocarter/sqlx/actions/workflows/php.yml/badge.svg?branch=main)
![code-coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen.svg?longCache=true&style=flat)

## Quick Start

```php
<?php

use MNC\SQLX\Engine\Metadata as SQLX;
use MNC\SQLX\SQL\Connection\PDOWrapper;
use MNC\SQLX\Engine;
use Castor\Context;

// We just need minimal information to map your class to a database table.
// The entity annotation and the id columns
// All the other things can be guessed using typing information

#[SQLX\Entity]
class Account
{
    #[SQLX\Id]
    private int $id;
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
}

// Bootstrapping the engine is simple
$conn = PDOWrapper::from(new PDO('sqlite::memory'));
$engine = Engine::configure($conn)
    ->withNamer(new Engine\Namer\Underscore())
    ->build()
;

$ctx = Context\nil();
$account = new Account('jdoe', 'jdoe@example.com', 'secret');

// Persisting a new object causes an insert:
// INSERT INTO account (username, email, password, created_at) VALUES ('jdoe', 'jdoe@example.com', 'secret')
$engine->persist($ctx, $account);

// Upon insertion, we fetch the last inserted id
echo $user->getId(); // (int) 1 

// Persisting an existing or "known" object causes an update:
// UPDATE account SET username = 'jdoe', email = 'jdoe@example.com', password = 'secret' WHERE id = 1
$engine->persist($ctx, $account);
```

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

Therefore, I would like to avoid full relationship support.

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