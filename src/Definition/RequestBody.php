<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class RequestBody
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class RequestBody implements DefinitionInterface
{

    /**
     * RequestBody constructor.
     *
     * @param string|null $description
     * @param array|null $content
     * @param bool|null $required
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public ?string $description = null,
        public ?array $content = null,
        public ?bool $required = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'description' => $this->description,
            'content' => $this->content,
            'required' => $this->required
        ], DefinitionHelper::isNotNull(...));
    }

}
