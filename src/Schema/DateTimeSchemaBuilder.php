<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use DateTimeInterface;
use Raxos\Contract\OpenAPI\SchemaBuilderInterface;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\{SchemaType, StringFormat};
use Raxos\OpenAPI\SchemaBuilder;
use function is_subclass_of;

/**
 * Class DateTimeSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class DateTimeSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null
    {
        return new Schema(
            type: SchemaType::STRING,
            nullable: $nullable,
            format: StringFormat::DATE_TIME
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool
    {
        return is_subclass_of($types[0], DateTimeInterface::class);
    }

}
