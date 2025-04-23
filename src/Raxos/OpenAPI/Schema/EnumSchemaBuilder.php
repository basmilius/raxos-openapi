<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use BackedEnum;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Contract\SchemaBuilderInterface;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\SchemaBuilder;
use function array_map;
use function assert;
use function is_string;
use function is_subclass_of;

/**
 * Class EnumSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class EnumSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null
    {
        $enum = $types[0];

        assert(is_subclass_of($enum, BackedEnum::class));

        $value = $enum::cases()[0]->value;

        return new Schema(
            type: is_string($value) ? SchemaType::STRING : SchemaType::INTEGER,
            nullable: false,
            enum: array_map(static fn(BackedEnum $value) => $value->value, $enum::cases()),
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 22-04-2025
     */
    public static function can(array $types): bool
    {
        return is_subclass_of($types[0], BackedEnum::class);
    }

}
