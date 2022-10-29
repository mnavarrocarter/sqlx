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

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    public const META_FLAGS = 'flags';
    public const FLAG_ID = 1;
    public const FLAG_AUTOINCREMENT = 2;

    public const META_SCOPE = 'scope';

    /**
     * The property type.
     */
    public string $type;

    /**
     * The column name.
     */
    public string $column;

    /**
     * The property name.
     */
    public string $name;

    /**
     * Whether the field is nullable of not.
     */
    public bool $nullable;

    /**
     * The field's default value.
     */
    public mixed $default;

    /**
     * The labels of the field.
     *
     * @var array<string,mixed>
     */
    public array $meta = [];

    /**
     * @param string[] $labels
     */
    public function __construct(
        string $type = '',
        string $column = '',
        bool $nullable = false,
        mixed $default = null,
        string $name = '',
        array $labels = []
    ) {
        $this->type = $type;
        $this->column = $column;
        $this->name = $name;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->meta = $labels;
    }

    public function isId(): bool
    {
        return (($this->meta[self::META_FLAGS] ?? 0) & self::FLAG_ID) !== 0;
    }

    public function isAutoincrement(): bool
    {
        return (($this->meta[self::META_FLAGS] ?? 0) & self::FLAG_AUTOINCREMENT) !== 0;
    }
}
