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
use Raxos\OpenAPI\Schema\{BuiltinSchemaBuilder, ClassSchemaBuilder, DateTimeSchemaBuilder, EnumSchemaBuilder, FloatSchemaBuilder, IntegerSchemaBuilder, JsonSchemaBuilder, ModelSchemaBuilder, RequestModelSchemaBuilder, StringSchemaBuilder};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function in_array;
use function is_subclass_of;
use function json_encode;
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
        try {
            $class = new ReflectionClass($class);

            /** @var ReflectionAttribute<Attr\Schema> $schemaAttr */
            $schemaAttr = $class->getAttributes(Attr\Schema::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
            $schemaAttr = $schemaAttr?->newInstance();

            if ($schemaAttr === null) {
                return;
            }

            $schema = match (true) {
                JsonSchemaBuilder::can([$class->name]) => singleton(JsonSchemaBuilder::class)->build($this, $schemaAttr, [$class->name], $nullable),
                default => singleton(ClassSchemaBuilder::class)->build($this, $schemaAttr, [$class->name], $nullable),
            };

            $this->schemas->set($this->schemaId($class->name), $schema);
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($err);
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
        $nullable = ($types[1] ?? false) === 'null';

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
        static $nullableSchema = new Schema(nullable: true);

        $schemaId = $this->schemaId($class);
        $ref = new Reference("#/components/schemas/{$schemaId}");

        if ($this->schemas->has($schemaId)) {
            $schema = $this->schemas->get($schemaId);

            if ($nullable && $schema->nullable !== true) {
                return new Schema(
                    anyOf: [
                        $ref,
                        $nullableSchema
                    ]
                );
            }

            return $ref;
        }

        $this->build($class, $nullable);

        if (!$this->schemas->has($schemaId)) {
            return null;
        }

        return $this->reference($class, $nullable);
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
        $direct = match (true) {
            DateTimeSchemaBuilder::can($types) => singleton(DateTimeSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            ModelSchemaBuilder::can($types) => singleton(ModelSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            RequestModelSchemaBuilder::can($types) => singleton(RequestModelSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            default => null
        };

        if ($direct !== null) {
            return $direct;
        }

        $reference = match (true) {
            EnumSchemaBuilder::can($types) => singleton(EnumSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            JsonSchemaBuilder::can($types) => singleton(JsonSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            default => null
        };

        if ($reference !== null) {
            $schemaId = $this->schemaId($types[0]);
            $this->schemas->set($schemaId, $reference);

            return new Reference("#/components/schemas/{$schemaId}");
        }

        return match (true) {
            FloatSchemaBuilder::can($types) => singleton(FloatSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            IntegerSchemaBuilder::can($types) => singleton(IntegerSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            StringSchemaBuilder::can($types) => singleton(StringSchemaBuilder::class)->build($this, $schemaAttr, $types, $nullable),
            default => new Schema(
                type: SchemaType::STRING,
                pattern: json_encode($types)
            )
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
