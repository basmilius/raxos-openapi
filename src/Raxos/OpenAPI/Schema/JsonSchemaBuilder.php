<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Contract\SchemaBuilderInterface;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\OpenAPIException;
use Raxos\OpenAPI\SchemaBuilder;
use ReflectionClass;
use ReflectionException;
use Throwable;
use function array_map;
use function array_merge;
use function class_exists;
use function count;
use function explode;
use function is_array;
use function is_subclass_of;
use function preg_match_all;
use function str_replace;
use function str_starts_with;

/**
 * Class StringSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class JsonSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null
    {
        try {
            $class = new ReflectionClass($types[0]);
            $method = $class->getMethod('jsonSerialize');
            $shapeAttr = $method->getAttributes(ArrayShape::class)[0] ?? null;

            if ($shapeAttr === null) {
                return new Schema(
                    type: SchemaType::OBJECT
                );
            }

            $properties = [];
            $shape = $shapeAttr->getArguments()[0] ?? [];

            foreach ($shape as $key => $type) {
                $schema = $this->ofType($builder, $type);

                if ($schema === null) {
                    continue;
                }

                $properties[$key] = $schema;
            }

            return new Schema(
                type: SchemaType::OBJECT,
                properties: $properties
            );
        } catch (ReflectionException $err) {
            throw OpenAPIException::reflection($err);
        }
    }

    /**
     * Builds a schema for a type.
     *
     * @param SchemaBuilder $builder
     * @param string $type
     *
     * @return Schema|null
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function ofType(SchemaBuilder $builder, string $type): ?Schema
    {
        /** @noinspection RegExpRedundantEscape */
        /** @noinspection RegExpUnnecessaryNonCapturingGroup */
        $pattern = '/(?:^|\n)(?:array<string,\s*)?([\\\\a-zA-Z]+(?:\\\\[a-zA-Z]+)*(?:\|[\\\\a-zA-Z]+(?:\\\\[a-zA-Z]+)*)*(?:\[\])?|[^>]+)(?:>)?/m';

        preg_match_all($pattern, $type, $matches);
        $types = [];
        foreach ($matches[1] as $match) {
            $match = str_replace('[]', '', $match);
            $types[] = explode('|', $match);
        }

        $types = array_merge(...$types);

        if (empty($types)) {
            return null;
        }

        if (str_starts_with($type, 'array<')) {
            return new Schema(
                type: SchemaType::ARRAY,
                items: $this->resolve($builder, $types)
            );
        }

        if (count($types) === 1 || count($types) === 2) {
            $schemaType = match ($types[0]) {
                'array' => SchemaType::ARRAY,
                'bool' => SchemaType::BOOLEAN,
                'float' => SchemaType::NUMBER,
                'int' => SchemaType::INTEGER,
                'string' => SchemaType::STRING,
                Throwable::class => null,
                default => $this->resolve($builder, $types[0])
            };

            if ($schemaType === null) {
                return null;
            }

            if ($schemaType instanceof Schema) {
                return $schemaType;
            }

            return new Schema(
                type: $schemaType,
                nullable: ($types[1] ?? false) === 'null'
            );
        }

        return new Schema(
            oneOf: array_map(static fn(string $type) => $this->ofType($builder, $type), $types)
        );
    }

    /**
     * Resolves a subtype.
     *
     * @param SchemaBuilder $builder
     * @param string[]|string $type
     *
     * @return Schema|null
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function resolve(SchemaBuilder $builder, array|string $type): ?Schema
    {
        if (is_array($type)) {
            if (count($type) > 1) {
                return new Schema(
                    anyOf: array_map(fn(string $type) => $this->resolve($builder, $type), $type)
                );
            }

            $type = $type[0];
        }

        if (!class_exists($type)) {
            return null;
        }

        if (is_subclass_of($type, JsonSerializable::class)) {
            return $this->build($builder, new Attr\Model(), [$type], false);
        }

        return $builder->reference($type);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool
    {
        if (!is_subclass_of($types[0], JsonSerializable::class)) {
            return false;
        }

        $class = new ReflectionClass($types[0]);
        $method = $class->getMethod('jsonSerialize');

        $shapeAttr = $method->getAttributes(ArrayShape::class)[0] ?? null;

        return $shapeAttr !== null;
    }

}
