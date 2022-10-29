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

use MNC\SQLX\Engine\Metadata;
use MNC\SQLX\Engine\Namer;
use MNC\SQLX\Engine\PropertyAccessor;
use MNC\SQLX\Engine\Tracker;
use MNC\SQLX\SQL\Connection;
use MNC\SQLX\SQL\Mapper;

class Configuration
{
    /**
     * The database connection to use fot this.
     */
    private Connection $connection;

    private ?Namer $namer;

    private ?Tracker $tracker;

    private ?Metadata\Store $metadataStore;

    private ?PropertyAccessor\Store $accessorStore;

    /**
     * @var array<int, callable(Mapper):Mapper>
     */
    private array $mappers;

    public function __construct(
        Connection $connection,
        ?Namer $namer = null,
        ?Tracker $tracker = null,
        ?Metadata\Store $metadataStore = null,
        ?PropertyAccessor\Store $accessorStore = null,
    ) {
        $this->connection = $connection;
        $this->namer = $namer;
        $this->tracker = $tracker;
        $this->metadataStore = $metadataStore;
        $this->accessorStore = $accessorStore;
        $this->mappers = [];
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getNamer(): ?Namer
    {
        return $this->namer;
    }

    public function getTracker(): ?Tracker
    {
        return $this->tracker;
    }

    public function getAccessorStore(): ?PropertyAccessor\Store
    {
        return $this->accessorStore;
    }

    public function getMetadataStore(): ?Metadata\Store
    {
        return $this->metadataStore;
    }

    /**
     * @return array<int,callable(Mapper):Mapper
     */
    public function getMappers(): array
    {
        return $this->mappers;
    }

    /**
     * @return $this
     */
    public function withNamer(Namer $namer): Configuration
    {
        $this->namer = $namer;

        return $this;
    }

    /**
     * @param callable(Mapper): Mapper $mapperFn
     *
     * @return $this
     */
    public function withMapper(callable $mapperFn): Configuration
    {
        $this->mappers[] = $mapperFn;

        return $this;
    }

    public function build(): Engine
    {
        return Engine::make($this);
    }
}
