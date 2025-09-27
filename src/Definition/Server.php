<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Server
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Server implements DefinitionInterface
{

    /**
     * Server conDefinitionor.
     *
     * @param string $url
     * @param string|null $description
     * @param array<string, ServerVariable>|null $variables
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $url,
        public ?string $description = null,
        public ?array $variables = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'url' => $this->url,
            'description' => $this->description,
            'variables' => $this->variables
        ], DefinitionHelper::isNotNull(...));
    }

}
