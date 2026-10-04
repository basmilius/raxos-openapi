<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\Http\Validate\ConstraintAttributeInterface;
use Raxos\Http\Validate\Constraint\Choice;
use Raxos\Http\Validate\Constraint\Date;
use Raxos\Http\Validate\Constraint\DateTime;
use Raxos\Http\Validate\Constraint\Email;
use Raxos\Http\Validate\Constraint\Max;
use Raxos\Http\Validate\Constraint\MaxLength;
use Raxos\Http\Validate\Constraint\Min;
use Raxos\Http\Validate\Constraint\MinLength;
use Raxos\Http\Validate\Constraint\Model;
use Raxos\Http\Validate\Constraint\ModelArray;
use Raxos\Http\Validate\Constraint\Nested;
use Raxos\Http\Validate\Constraint\NestedArray;
use Raxos\Http\Validate\Constraint\Time;
use Raxos\Http\Validate\Constraint\Url;
use Raxos\Http\Validate\RequestPropertyMetadata;
use Raxos\OpenAPI\Attribute\Property as PropertyAttribute;
use Raxos\OpenAPI\Attribute\Required;
use Raxos\OpenAPI\Attribute\Schema as SchemaAttribute;
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Enum\StringFormat;
use Raxos\OpenAPI\SchemaBuilder;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use function array_filter;
use function array_replace;
use function array_values;
use function get_object_vars;
use function is_subclass_of;
use function max;

/**
 * Class RequestSchemaBuilder
 *
 * Builds input components independently from response aliases and explicit schema overrides.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 3.3.0
 */
final class RequestSchemaBuilder
{
    /**
     * Infers input properties without evaluating optional rules that depend on runtime context.
     *
     * @param SchemaBuilder $builder
     * @param class-string $model
     *
     * @return Schema
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public static function build(
        SchemaBuilder $builder,
        string $model
    ): Schema
    {
        $class = new ReflectionClass($model);
        $classSchema = ($class->getAttributes(SchemaAttribute::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null)?->newInstance();

        if ($classSchema?->schema !== null) {
            return $classSchema->schema;
        }

        $properties = [];
        $required = [];
        $conditional = [];

        foreach ($class->getProperties() as $property) {
            $metadata = RequestPropertyMetadata::from($property, $class);
            $attribute = ($property->getAttributes(SchemaAttribute::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null)?->newInstance();

            if ($metadata === null && $attribute === null) {
                continue;
            }

            $name = $metadata?->name() ?? $attribute?->alias ?? $property->name;
            $override = ($property->getAttributes(Required::class)[0] ?? null)?->newInstance();
            $optional = $metadata?->optional();
            $inferredRequired = $metadata !== null && ($optional === false || ($optional === true && !$metadata->canDefault()));

            if ($override?->required === true || ($override === null && $inferredRequired)) {
                $required[] = $name;
            }

            if ($metadata !== null && $optional === null && $override === null) {
                $conditional[] = $name;
            }

            $properties[$name] = $attribute?->schema
                ?? ($metadata === null
                    ? $builder->property($property, $attribute)
                    : self::propertySchema($builder, $metadata, $attribute, $model));
        }

        if ($conditional !== []) {
            $builder->diagnostics->set($model, 'Conditional optional rules require an explicit Required attribute.');
        }

        return new Schema(
            type: SchemaType::OBJECT,
            properties: $properties,
            required: $required === [] ? null : $required,
            extensions: $conditional === [] ? [] : ['x-raxos-conditional-required' => $conditional]
        );
    }

    /**
     * Combines inferred input types with supported constraints and records unmapped runtime rules.
     *
     * @param SchemaBuilder $builder
     * @param RequestPropertyMetadata $metadata
     * @param PropertyAttribute|null $attribute
     * @param class-string $model
     *
     * @return Schema
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    private static function propertySchema(
        SchemaBuilder $builder,
        RequestPropertyMetadata $metadata,
        ?PropertyAttribute $attribute,
        string $model
    ): Schema
    {
        $optional = $metadata->optional();
        $nullable = $optional !== false && $metadata->canDefault();
        $types = array_values(array_filter($metadata->types, static fn(string $type): bool => $type !== 'null'));
        $base = is_subclass_of($types[0] ?? '', HttpRequestModelInterface::class)
            ? $builder->requestReference($types[0], $nullable)
            : $builder->auto($attribute ?? new PropertyAttribute(), $types, $nullable);
        $values = $base instanceof Schema ? get_object_vars($base) : ['allOf' => [$base]];
        $unmapped = [];

        foreach ($metadata->constraints as $constraintAttribute) {
            $constraint = $constraintAttribute->newInstance();
            $mapped = self::constraintValues($builder, $constraint, $nullable);

            if ($mapped === null) {
                $unmapped[] = $constraint::class;

                continue;
            }

            if ($constraint instanceof NestedArray || $constraint instanceof Model || $constraint instanceof ModelArray) {
                $values = $mapped;
            } else {
                $values = array_replace($values, $mapped);
            }
        }

        if ($metadata->hasDefault && $optional === true) {
            $values['default'] = $metadata->default;
        }

        if (($types[0] ?? null) === 'string' && $optional === false) {
            $values['minLength'] = max(1, $values['minLength'] ?? 0);
        }

        if ($unmapped !== []) {
            $values['extensions']['x-raxos-runtime-constraints'] = $unmapped;
            $builder->diagnostics->set($model . '::' . $metadata->property->name, 'Runtime constraints require an explicit schema for an exact representation.');
        }

        return new Schema(...$values);
    }

    /**
     * Maps only rules with a known schema representation; null leaves an explicit diagnostic.
     *
     * @param SchemaBuilder $builder
     * @param ConstraintAttributeInterface $constraint
     * @param bool $nullable
     *
     * @return array<string, mixed>|null
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    private static function constraintValues(
        SchemaBuilder $builder,
        ConstraintAttributeInterface $constraint,
        bool $nullable
    ): ?array
    {
        if ($constraint instanceof NestedArray) {
            return ['type' => SchemaType::ARRAY, 'items' => $builder->requestReference($constraint->propertyType), 'nullable' => $nullable];
        }

        if ($constraint instanceof Model || $constraint instanceof ModelArray) {
            $identifier = new Schema(anyOf: [new Schema(type: SchemaType::INTEGER), new Schema(type: SchemaType::STRING)]);

            return $constraint instanceof ModelArray
                ? ['type' => SchemaType::ARRAY, 'items' => $identifier, 'nullable' => $nullable]
                : ['anyOf' => [$identifier], 'nullable' => $nullable];
        }

        return match (true) {
            $constraint instanceof MinLength => ['minLength' => $constraint->min],
            $constraint instanceof MaxLength => ['maxLength' => $constraint->max],
            $constraint instanceof Min => ['minimum' => $constraint->min],
            $constraint instanceof Max => ['maximum' => $constraint->max],
            $constraint instanceof Choice => ['enum' => $constraint->options],
            $constraint instanceof Email => ['format' => StringFormat::EMAIL],
            $constraint instanceof Url => ['format' => StringFormat::URI],
            $constraint instanceof Date => ['format' => StringFormat::DATE],
            $constraint instanceof DateTime => ['format' => StringFormat::DATE_TIME],
            $constraint instanceof Time => ['format' => StringFormat::TIME],
            $constraint instanceof Nested => [],
            default => null
        };
    }
}
