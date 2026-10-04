<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Raxos\Contract\OpenAPI\SchemaBuilderInterface;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\Reference;
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\OpenAPI\SchemaBuilder;
use ReflectionClass;
use ReflectionException;
use Throwable;
use function array_map;
use function count;
use function is_subclass_of;
use function preg_match;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * Class JsonSchemaBuilder
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
    public function build(
        SchemaBuilder $builder,
        Attr\Schema $schemaAttr,
        array $types,
        bool $nullable
    ): Reference|Schema|null
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
            throw new ReflectionErrorException($err);
        }
    }

    /**
     * Resolves ArrayShape types, preserving nested lists, dictionaries and unions.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function ofType(
        SchemaBuilder $builder,
        string $type
    ): Reference|Schema|null
    {
        $type = trim($type);

        if ($type === Throwable::class) {
            return null;
        }

        if (str_starts_with($type, '?')) {
            $type = substr($type, 1) . '|null';
        }

        $types = $this->split($type, '|');

        if (count($types) > 1) {
            return new Schema(anyOf: array_map(fn(string $type): Reference|Schema|null => $this->ofType($builder, $type), $types));
        }

        if (str_ends_with($type, '[]')) {
            return new Schema(type: SchemaType::ARRAY, items: $this->ofType($builder, substr($type, 0, -2)));
        }

        if (preg_match('/^(array|list)<(.+)>$/', $type, $matches) === 1) {
            $parts = $this->split($matches[2], ',');
            $value = $parts[count($parts) - 1];
            $schema = $this->ofType($builder, $value);

            return count($parts) === 2 && $parts[0] === 'string'
                ? new Schema(type: SchemaType::OBJECT, additionalProperties: $schema)
                : new Schema(type: SchemaType::ARRAY, items: $schema);
        }

        return $builder->auto(new Attr\Property(), [$type]);
    }

    /**
     * Splits type expressions only outside nested generic brackets.
     *
     * @return string[]
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private function split(
        string $type,
        string $separator
    ): array
    {
        $parts = [];
        $depth = 0;
        $start = 0;
        $length = strlen($type);

        for ($index = 0; $index < $length; ++$index) {
            $depth += match ($type[$index]) {
                '<', '(' => 1,
                '>', ')' => -1,
                default => 0
            };

            if ($type[$index] === $separator && $depth === 0) {
                $parts[] = trim(substr($type, $start, $index - $start));
                $start = $index + 1;
            }
        }

        $parts[] = trim(substr($type, $start));

        return $parts;
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
