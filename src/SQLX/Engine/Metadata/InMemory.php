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

namespace MNC\SQLX\Engine\Metadata;

use MNC\SQLX\Engine\Metadata;

final class InMemory implements Metadata
{
    private string $tableName;
    private string $className;

    /**
     * @var callable():object
     */
    private $instanceFactory;

    /**
     * @var Field[]
     */
    private array $fields = [];

    /**
     * @var array<string,int>
     */
    private array $propIndex = [];

    /**
     * @var array<string,int>
     */
    private array $colIndex = [];

    public function __construct(
        string $className,
        string $tableName,
        callable $instanceFactory,
    ) {
        $this->tableName = $tableName;
        $this->className = $className;
        $this->instanceFactory = $instanceFactory;
    }

    public function addField(Field $field): void
    {
        $count = array_push($this->fields, $field);
        $this->propIndex[$field->name] = $count - 1;
        $this->colIndex[$field->column] = $count - 1;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getFieldByProp(string $name): ?Field
    {
        return $this->fields[$this->propIndex[$name] ?? -1] ?? null;
    }

    public function getFieldByColumn(string $name): ?Field
    {
        return $this->fields[$this->colIndex[$name] ?? -1] ?? null;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function newInstance(): object
    {
        return ($this->instanceFactory)();
    }
}
