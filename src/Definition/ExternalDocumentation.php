<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class ExternalDocumentation
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.7.0
 */
final readonly class ExternalDocumentation implements DefinitionInterface
{

    /**
     * ExternalDocumentation conDefinitionor.
     *
     * @param string $description
     * @param string $url
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $description,
        public string $url
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'description' => $this->description,
            'url' => $this->url
        ], DefinitionHelper::isNotNull(...));
    }

}
