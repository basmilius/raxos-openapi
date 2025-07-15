<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Response
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Response implements DefinitionInterface
{

    /**
     * Response conDefinitionor.
     *
     * @param string|null $description
     * @param array<string, string|string[]>|null $headers
     * @param array|null $content
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public ?string $description = null,
        public ?array $headers = null,
        public ?array $content = null
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
            'headers' => $this->headers,
            'content' => $this->content
        ], DefinitionHelper::isNotNull(...));
    }

}
