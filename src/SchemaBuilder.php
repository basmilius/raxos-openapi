<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use JsonSerializable;
use Raxos\Database\Orm\Attribute as ORM;
use Raxos\Foundation\Collection\Map;
use Raxos\Foundation\Contract\MapInterface;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{MediaType, Reference, Response, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\OpenAPIException;
use Raxos\OpenAPI\Schema\{ClassSchemaBuilder, DateTimeSchemaBuilder, EnumSchemaBuilder, FloatSchemaBuilder, IntegerSchemaBuilder, JsonSchemaBuilder, ModelSchemaBuilder, RequestModelSchemaBuilder, StringSchemaBuilder};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function is_subclass_of;
use function json_encode;

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
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(string $class, bool $nullable = false): void
    {
        try {
            $class = new ReflectionClass($class);
            $schemaAttr = $class->getAttributes(Attr\Schema::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
            $schemaAttr = $schemaAttr?->newInstance();

            if ($schemaAttr === null) {
                return;
            }

            $schema = match (true) {
                JsonSchemaBuilder::can([$class->name]) => new JsonSchemaBuilder()->build($this, $schemaAttr, [$class->name], $nullable),
                default => new ClassSchemaBuilder()->build($this, $schemaAttr, [$class->name], $nullable),
            };

            $this->schemas->set($class->name, $schema);
        } catch (ReflectionException $err) {
            throw OpenAPIException::reflection($err);
        }
    }

    /**
     * Generates the schemas for the properties of the class.
     *
     * @param ReflectionClass $class
     *
     * @return Generator<string, Reference|Schema>
     * @throws OpenAPIException
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
            $columnAttr = $property->getAttributes(ORM\Column::class)[0] ?? null;
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
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function property(ReflectionProperty $property, Attr\Schema $schemaAttr): Reference|Schema|null
    {
        $types = ReflectionUtil::getTypes($property->getType());
        $nullable = ($types[1] ?? false) === 'null';

        return $this->buildSchema($schemaAttr, $types, $nullable);
    }

    /**
     * Returns a reference to a schema.
     *
     * @param string $class
     * @param bool $nullable
     *
     * @return Reference|Schema|null
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function reference(string $class, bool $nullable = false): Reference|Schema|null
    {
        static $nullableSchema = new Schema(nullable: true);

        $ref = new Reference("#/components/schemas/{$class}");

        if ($this->schemas->has($class)) {
            $schema = $this->schemas->get($class);

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

        $this->build($class);

        if (!$this->schemas->has($class)) {
            return null;
        }

        return $this->reference($class, $nullable);
    }

    /**
     * Returns a response or a reference to a response.
     *
     * @param Attr\Response $responseAttr
     *
     * @return Reference|Response|null
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function response(Attr\Response $responseAttr): Reference|Response|null
    {
        $content = null;

        if ($responseAttr->model !== null && is_subclass_of($responseAttr->model, JsonSerializable::class)) {
            if ($this->responses->has($responseAttr->model)) {
                return new Reference("#/components/responses/{$responseAttr->model}");
            }

            $schema = $this->reference($responseAttr->model);

            if ($schema !== null) {
                $content = [];
                $content['application/json'] = new MediaType(
                    $this->reference($responseAttr->model)
                );
            }

            $this->responses->set($responseAttr->model, new Response(
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
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function buildSchema(Attr\Schema $schemaAttr, array $types, bool $nullable = false): Reference|Schema|null
    {
        $direct = match (true) {
            DateTimeSchemaBuilder::can($types) => new DateTimeSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            ModelSchemaBuilder::can($types) => new ModelSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            RequestModelSchemaBuilder::can($types) => new RequestModelSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            default => null
        };

        if ($direct !== null) {
            return $direct;
        }

        $reference = match (true) {
            EnumSchemaBuilder::can($types) => new EnumSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            JsonSchemaBuilder::can($types) => new JsonSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            default => null
        };

        if ($reference !== null) {
            $this->schemas->set($types[0], $reference);

            return new Reference("#/components/schemas/{$types[0]}");
        }

        return match (true) {
            FloatSchemaBuilder::can($types) => new FloatSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            IntegerSchemaBuilder::can($types) => new IntegerSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            StringSchemaBuilder::can($types) => new StringSchemaBuilder()->build($this, $schemaAttr, $types, $nullable),
            default => new Schema(
                type: SchemaType::STRING,
                pattern: json_encode($types)
            )
        };
    }

}
