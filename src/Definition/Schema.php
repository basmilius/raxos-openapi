<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use Raxos\OpenAPI\Enum\{NumberFormat, SchemaType, StringFormat};
use function array_filter;

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
     * @param SchemaType|null $type
     * @param bool|null $deprecated
     * @param bool|null $nullable
     * @param bool|null $readOnly
     * @param bool|null $writeOnly
     * @param Schema[]|null $allOf
     * @param Schema[]|null $anyOf
     * @param Schema[]|null $oneOf
     * @param Schema|null $not
     * @param int|null $maxLength
     * @param int|null $minLength
     * @param string|null $pattern
     * @param NumberFormat|StringFormat|null $format
     * @param string[]|null $enum
     * @param int|null $maximum
     * @param int|null $minimum
     * @param bool|null $exclusiveMaximum
     * @param bool|null $exclusiveMinimum
     * @param int|null $multipleOf
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
     * @since 1.8.0
     */
    public function __construct(
        public ?SchemaType $type = null,
        public ?bool $deprecated = null,
        public ?bool $nullable = null,
        public ?bool $readOnly = null,
        public ?bool $writeOnly = null,
        public ?array $allOf = null,
        public ?array $anyOf = null,
        public ?array $oneOf = null,
        public ?Schema $not = null,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public ?string $pattern = null,
        public NumberFormat|StringFormat|null $format = null,
        public ?array $enum = null,
        public ?int $maximum = null,
        public ?int $minimum = null,
        public ?bool $exclusiveMaximum = null,
        public ?bool $exclusiveMinimum = null,
        public ?int $multipleOf = null,
        public ?int $maxItems = null,
        public ?int $minItems = null,
        public ?bool $uniqueItems = null,
        public Reference|Schema|null $items = null,
        public ?array $properties = null,
        public ?array $additionalProperties = null,
        public ?array $required = null,
        public ?int $maxProperties = null,
        public ?int $minProperties = null,
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'type' => $this->type,
            'deprecated' => $this->deprecated,
            'nullable' => $this->nullable,
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
            'exclusiveMaximum' => $this->exclusiveMaximum,
            'exclusiveMinimum' => $this->exclusiveMinimum,
            'multipleOf' => $this->multipleOf,
            'maxItems' => $this->maxItems,
            'minItems' => $this->minItems,
            'uniqueItems' => $this->uniqueItems,
            'items' => $this->items,
            'properties' => $this->properties,
            'additionalProperties' => $this->additionalProperties,
            'required' => $this->required,
            'maxProperties' => $this->maxProperties,
            'minProperties' => $this->minProperties
        ], DefinitionHelper::isNotEmpty(...));
    }

}
