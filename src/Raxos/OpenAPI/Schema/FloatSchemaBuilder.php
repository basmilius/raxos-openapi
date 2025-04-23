<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Contract\SchemaBuilderInterface;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\{NumberFormat, SchemaType};
use Raxos\OpenAPI\SchemaBuilder;

/**
 * Class IntegerSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class FloatSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null
    {
        return new Schema(
            type: SchemaType::NUMBER,
            nullable: $nullable,
            format: NumberFormat::FLOAT
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool
    {
        return $types[0] === 'float';
    }

}
