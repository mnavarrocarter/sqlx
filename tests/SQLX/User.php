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

use MNC\SQLX\Engine\Metadata as SQLX;

#[SQLX\Entity]
class User extends Entity
{
    #[SQLX\Id]
    private int $id;
    private int $tenantId;
    private string $name;
    private string $email;
    private string $password;

    public function __construct(int $tenantId, string $name, string $email, string $password)
    {
        $this->id = 0;
        $this->tenantId = $tenantId;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        parent::__construct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }
}
