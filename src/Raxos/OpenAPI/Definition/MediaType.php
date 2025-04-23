<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class MediaType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class MediaType implements DefinitionInterface
{

    /**
     * MediaType constructor.
     *
     * @param Schema|Reference $schema
     * @param mixed|null $example
     * @param array<string, Example|Reference>|null $examples
     * @param array<string, Encoding>|null $encoding
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public Schema|Reference $schema,
        public mixed $example = null,
        public ?array $examples = null,
        public ?array $encoding = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'schema' => $this->schema,
            'example' => $this->example,
            'examples' => $this->examples,
            'encoding' => $this->encoding
        ], DefinitionHelper::isNotEmpty(...));
    }

}
