<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use JsonSerializable;
use Raxos\Collection\Map;
use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\OpenAPI\OpenAPIExceptionInterface;
use Raxos\Database\Orm\Attribute as ORM;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{MediaType, Reference, Response, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\OpenAPI\Schema\{BuiltinSchemaBuilder, ClassSchemaBuilder, DateTimeSchemaBuilder, EnumSchemaBuilder, FloatSchemaBuilder, IntegerSchemaBuilder, JsonSchemaBuilder, StringSchemaBuilder};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function array_filter;
use function array_map;
use function array_values;
use function class_exists;
use function count;
use function enum_exists;
use function in_array;
use function is_subclass_of;
use function Raxos\Foundation\singleton;
use function str_replace;

/**
 * Class SchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.8.0
 */
final readonly class SchemaBuilder
{

    /**
     * SchemaBuilder constructor.
     *
     * @param MapInterface<string, Response> $responses
     * @param MapInterface<string, Schema> $schemas
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public private(set) MapInterface $responses = new Map(),
        public private(set) MapInterface $schemas = new Map()
    ) {}

    /**
     * Builds a schema for the class.
     *
     * @param class-string $class
     * @param bool $nullable
     *
     * @return void
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(string $class, bool $nullable = false): void
    {
        $schemaId = $this->schemaId($class);

        if ($this->schemas->has($schemaId)) {
            return;
        }

        try {
            $class = new ReflectionClass($class);
            $schemaAttr = $class->getAttributes(Attr\Schema::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
            $schemaAttr = $schemaAttr?->newInstance();
            $isEnum = EnumSchemaBuilder::can([$class->name]);
            $isJson = JsonSchemaBuilder::can([$class->name]);

            if ($schemaAttr === null && !$isEnum && !$isJson) {
                return;
            }

            $schemaAttr ??= new Attr\Model();

            // Reserve the component before traversing properties that may point back to it.
            $this->schemas->set($schemaId, new Schema(type: SchemaType::OBJECT));
            $schema = match (true) {
                $isEnum => singleton(EnumSchemaBuilder::class)->build($this, $schemaAttr, [$class->name], false),
                $isJson => singleton(JsonSchemaBuilder::class)->build($this, $schemaAttr, [$class->name], false),
                default => singleton(ClassSchemaBuilder::class)->build($this, $schemaAttr, [$class->name], false),
            };
            $this->schemas->set($schemaId, $schema);
        } catch (ReflectionException $err) {
            $this->schemas->unset($schemaId);
            throw new ReflectionErrorException($err);
        } catch (\Throwable $err) {
            $this->schemas->unset($schemaId);
            throw $err;
        }
    }

    /**
     * Builds a schema for a builtin type.
     *
     * @param Attr\Response $responseAttr
     *
     * @return Reference|Response|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    public function buildBuiltIn(Attr\Response $responseAttr): Reference|Response|Schema|null
    {
        return BuiltinSchemaBuilder::build($this, $responseAttr->model, $responseAttr->modelGeneric);
    }

    /**
     * Generates the schemas for the properties of the class.
     *
     * @param ReflectionClass $class
     *
     * @return Generator<string, Reference|Schema>
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function properties(ReflectionClass $class): Generator
    {
        foreach ($class->getProperties() as $property) {
            /** @var ReflectionAttribute<ORM\Column> $hiddenAttr */
            $hiddenAttr = $property->getAttributes(ORM\Hidden::class)[0] ?? null;

            if ($hiddenAttr !== null) {
                continue;
            }

            /** @var ReflectionAttribute<ORM\Alias> $aliasAttr */
            $aliasAttr = $property->getAttributes(ORM\Alias::class)[0] ?? null;
            $aliasAttr = $aliasAttr?->newInstance();

            /** @var ReflectionAttribute<ORM\Column> $columnAttr */
            $columnAttr = $property->getAttributes(ORM\Column::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
            $columnAttr = $columnAttr?->newInstance();

            /** @var ReflectionAttribute<Attr\Schema> $schemaAttr */
            $schemaAttr = $property->getAttributes(Attr\Schema::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            if ($schemaAttr === null) {
                continue;
            }

            $schemaAttr = $schemaAttr->newInstance();
            $name = ($aliasAttr !== null ? $columnAttr?->key : null)
                ?? $aliasAttr?->alias
                ?? $schemaAttr->alias
                ?? $property->name;

            if ($schemaAttr->schema !== null) {
                yield $name => $schemaAttr->schema;
                continue;
            }

            $schema = $this->property($property, $schemaAttr);

            if ($schema === null) {
                continue;
            }

            yield $name => $schema;
        }
    }

    /**
     * Returns the schema for a property.
     *
     * @param ReflectionProperty $property
     * @param Attr\Schema $schemaAttr
     *
     * @return Reference|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function property(ReflectionProperty $property, Attr\Schema $schemaAttr): Reference|Schema|null
    {
        $types = ReflectionUtil::getTypes($property->getType());
        $nullable = in_array('null', $types, true);

        return $this->auto($schemaAttr, $types, $nullable);
    }

    /**
     * Returns a reference to a schema.
     *
     * @param string $class
     * @param bool $nullable
     *
     * @return Reference|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function reference(string $class, bool $nullable = false): Reference|Schema|null
    {
        $schemaId = $this->schemaId($class);
        $this->build($class);

        if (!$this->schemas->has($schemaId)) {
            return null;
        }

        $ref = new Reference("#/components/schemas/{$schemaId}");

        return $nullable
            ? new Schema(anyOf: [$ref, new Schema(type: SchemaType::NULL)])
            : $ref;
    }

    /**
     * Returns a response or a reference to a response.
     *
     * @param Attr\Response $responseAttr
     *
     * @return Reference|Response|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function response(Attr\Response $responseAttr): Reference|Response|Schema|null
    {
        $content = $responseAttr->content;

        if ($responseAttr->model !== null && in_array($responseAttr->model, BuiltinSchemaBuilder::BUILTINS, true)) {
            return $this->buildBuiltIn($responseAttr);
        }

        if ($responseAttr->model !== null && is_subclass_of($responseAttr->model, JsonSerializable::class)) {
            $schemaId = $this->schemaId($responseAttr->model);

            if ($this->responses->has($schemaId)) {
                return new Reference("#/components/responses/{$schemaId}");
            }

            $schema = $this->reference($responseAttr->model);

            if ($schema !== null) {
                $content ??= [];
                $content['application/json'] = new MediaType($schema);
            }

            $this->responses->set($schemaId, new Response(
                description: $responseAttr->description,
                content: $content
            ));

            return $this->response($responseAttr);
        }

        return new Response(
            description: $responseAttr->description,
            content: $content
        );
    }

    /**
     * Builds a schema object based on the types.
     *
     * @param Attr\Schema $schemaAttr
     * @param array $types
     * @param bool $nullable
     *
     * @return Reference|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     * @internal
     */
    public function auto(Attr\Schema $schemaAttr, array $types, bool $nullable = false): Reference|Schema|null
    {
        $nullable = $nullable || in_array('null', $types, true);
        $types = array_values(array_filter($types, static fn(string $type): bool => $type !== 'null'));

        if ($types === []) {
            return new Schema(type: SchemaType::NULL);
        }

        if (count($types) > 1) {
            $schemas = array_map(fn(string $type): Reference|Schema|null => $this->auto($schemaAttr, [$type]), $types);

            if ($nullable) {
                $schemas[] = new Schema(type: SchemaType::NULL);
            }

            return new Schema(anyOf: $schemas);
        }

        if (DateTimeSchemaBuilder::can($types)) {
            return singleton(DateTimeSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable);
        }

        if (class_exists($types[0]) || enum_exists($types[0])) {
            return $this->reference($types[0], $nullable) ?? new Schema(type: SchemaType::OBJECT, nullable: $nullable);
        }

        return match ($types[0]) {
            'bool', 'boolean' => new Schema(type: SchemaType::BOOLEAN, nullable: $nullable),
            'true' => new Schema(type: SchemaType::BOOLEAN, nullable: $nullable, enum: [true]),
            'false' => new Schema(type: SchemaType::BOOLEAN, nullable: $nullable, enum: [false]),
            'array', 'iterable' => new Schema(type: SchemaType::ARRAY, nullable: $nullable),
            'object' => new Schema(type: SchemaType::OBJECT, nullable: $nullable),
            'float', 'double' => singleton(FloatSchemaBuilder::class)->build($this, $schemaAttr, ['float'], $nullable),
            'int', 'integer' => singleton(IntegerSchemaBuilder::class)->build($this, $schemaAttr, ['int'], $nullable),
            'string' => singleton(StringSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            default => new Schema(type: SchemaType::cases())
        };
    }

    /**
     * Returns the ID for the given class name.
     *
     * @param string $className
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    private function schemaId(string $className): string
    {
        return str_replace('\\', '.', $className);
    }

}
