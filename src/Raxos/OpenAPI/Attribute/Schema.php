<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Raxos\OpenAPI\Definition\Example;
use Raxos\OpenAPI\Definition\Schema as SchemaDefinition;

/**
 * Class Schema
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.8.0
 */
abstract readonly class Schema
{

    /**
     * Schema constructor.
     *
     * @param string|null $alias
     * @param mixed $example
     * @param Example[]|null $examples
     * @param SchemaDefinition|null $schema
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public ?string $alias = null,
        public mixed $example = null,
        public ?array $examples = null,
        public ?SchemaDefinition $schema = null
    ) {}

}
