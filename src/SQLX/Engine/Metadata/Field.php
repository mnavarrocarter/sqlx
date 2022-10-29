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
    public const LABEL_AUTOINCREMENT = 'autoincrement';
    public const LABEL_ID = 'id';

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
     * @var string[]
     */
    public array $labels = [];

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
        $this->labels = $labels;
    }

    public function isId(): bool
    {
        return in_array(self::LABEL_ID, $this->labels, true);
    }

    public function isAutoincrement(): bool
    {
        return in_array(self::LABEL_AUTOINCREMENT, $this->labels, true);
    }
}
