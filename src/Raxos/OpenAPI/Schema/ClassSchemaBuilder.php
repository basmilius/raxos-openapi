<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Contract\SchemaBuilderInterface;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\OpenAPIException;
use Raxos\OpenAPI\SchemaBuilder;
use ReflectionClass;
use ReflectionException;
use function class_exists;

/**
 * Class ClassSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class ClassSchemaBuilder implements SchemaBuilderInterface
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
            $properties = [];

            /** @noinspection PhpLoopCanBeConvertedToArrayMapInspection */
            foreach ($builder->properties($class) as $key => $property) {
                $properties[$key] = $property;
            }

            return new Schema(
                type: SchemaType::OBJECT,
                nullable: $nullable,
                properties: $properties
            );
        } catch (ReflectionException $err) {
            throw OpenAPIException::reflection($err);
        }
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool
    {
        return class_exists($types[0]);
    }

}
