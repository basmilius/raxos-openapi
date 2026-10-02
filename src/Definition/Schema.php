<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use Raxos\OpenAPI\Enum\{NumberFormat, SchemaType, StringFormat};
use stdClass;
use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function is_array;
use function is_bool;

/**
 * Class Schema
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class Schema implements DefinitionInterface
{

    /**
     * Schema constructor.
     *
     * @param SchemaType|SchemaType[]|null $type
     * @param bool|null $deprecated
     * @param bool|null $nullable
     * @param bool|null $readOnly
     * @param bool|null $writeOnly
     * @param Schema[]|null $allOf
     * @param Schema[]|null $anyOf
     * @param Schema[]|null $oneOf
     * @param Reference|Schema|null $not
     * @param int|null $maxLength
     * @param int|null $minLength
     * @param string|null $pattern
     * @param NumberFormat|StringFormat|null $format
     * @param array<int, string|int>|null $enum
     * @param int|float|null $maximum
     * @param int|float|null $minimum
     * @param int|float|bool|null $exclusiveMaximum
     * @param int|float|bool|null $exclusiveMinimum
     * @param int|float|null $multipleOf
     * @param int|null $maxItems
     * @param int|null $minItems
     * @param bool|null $uniqueItems
     * @param Reference|Schema|null $items
     * @param Schema[]|null $properties
     * @param Schema[]|null $additionalProperties
     * @param string[]|null $required
     * @param int|null $maxProperties
     * @param int|null $minProperties
     *
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function __construct(
        public SchemaType|array|null $type = null,
        public ?bool $deprecated = null,
        public ?bool $nullable = null,
        public ?bool $readOnly = null,
        public ?bool $writeOnly = null,
        public ?array $allOf = null,
        public ?array $anyOf = null,
        public ?array $oneOf = null,
        public Reference|Schema|null $not = null,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public ?string $pattern = null,
        public NumberFormat|StringFormat|null $format = null,
        public ?array $enum = null,
        public int|float|null $maximum = null,
        public int|float|null $minimum = null,
        public int|float|bool|null $exclusiveMaximum = null,
        public int|float|bool|null $exclusiveMinimum = null,
        public int|float|null $multipleOf = null,
        public ?int $maxItems = null,
        public ?int $minItems = null,
        public ?bool $uniqueItems = null,
        public Reference|Schema|null $items = null,
        public ?array $properties = null,
        public Reference|Schema|array|bool|null $additionalProperties = null,
        public ?array $required = null,
        public ?int $maxProperties = null,
        public ?int $minProperties = null,
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array|stdClass
    {
        $schema = array_filter([
            'type' => $this->type,
            'deprecated' => $this->deprecated,
            'readOnly' => $this->readOnly,
            'writeOnly' => $this->writeOnly,
            'allOf' => $this->allOf,
            'anyOf' => $this->anyOf,
            'oneOf' => $this->oneOf,
            'not' => $this->not,
            'maxLength' => $this->maxLength,
            'minLength' => $this->minLength,
            'pattern' => $this->pattern,
            'format' => $this->format,
            'enum' => $this->enum,
            'maximum' => $this->maximum,
            'minimum' => $this->minimum,
            'exclusiveMaximum' => is_bool($this->exclusiveMaximum) ? ($this->exclusiveMaximum ? $this->maximum : null) : $this->exclusiveMaximum,
            'exclusiveMinimum' => is_bool($this->exclusiveMinimum) ? ($this->exclusiveMinimum ? $this->minimum : null) : $this->exclusiveMinimum,
            'multipleOf' => $this->multipleOf,
            'maxItems' => $this->maxItems,
            'minItems' => $this->minItems,
            'uniqueItems' => $this->uniqueItems,
            'items' => $this->items,
            'properties' => $this->properties === null ? null : (object)$this->properties,
            'additionalProperties' => $this->additionalProperties,
            'required' => $this->required,
            'maxProperties' => $this->maxProperties,
            'minProperties' => $this->minProperties
        ], DefinitionHelper::isNotNull(...));

        if ($this->nullable === true) {
            if ($this->type !== null) {
                $types = is_array($this->type) ? $this->type : [$this->type];
                $types[] = SchemaType::NULL;
                $schema['type'] = array_values(array_unique(array_map(static fn(SchemaType $type): string => $type->value, $types)));

                if ($this->enum !== null) {
                    $schema['enum'][] = null;
                }
            } else {
                return $schema === []
                    ? ['type' => SchemaType::NULL]
                    : ['anyOf' => [$schema, ['type' => SchemaType::NULL]]];
            }
        }

        return $schema === [] ? new stdClass() : $schema;
    }

}
