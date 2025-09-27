<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\OpenAPI\SchemaBuilderInterface;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\SchemaBuilder;
use function is_subclass_of;

/**
 * Class RequestModelSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 1.8.0
 */
final readonly class RequestModelSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null
    {
        return $builder->reference($types[0], $nullable);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool
    {
        return is_subclass_of($types[0], HttpRequestModelInterface::class);
    }

}
