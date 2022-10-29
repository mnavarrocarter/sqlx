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


