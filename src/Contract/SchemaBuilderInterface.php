<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Contract;

use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Error\OpenAPIException;
use Raxos\OpenAPI\SchemaBuilder;

/**
 * Interface SchemaBuilderInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Contract
 * @since 1.8.0
 */
interface SchemaBuilderInterface
{

    /**
     * Builds the schema.
     *
     * @param SchemaBuilder $builder
     * @param Attr\Schema $schemaAttr
     * @param string[] $types
     * @param bool $nullable
     *
     * @return Reference|Schema|null
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(SchemaBuilder $builder, Attr\Schema $schemaAttr, array $types, bool $nullable): Reference|Schema|null;

    /**
     * Checks if the builder can build the schema.
     *
     * @param string[] $types
     *
     * @return bool
     * @throws OpenAPIException
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public static function can(array $types): bool;

}
