<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class License
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.7.0
 */
final readonly class License implements DefinitionInterface
{

    /**
     * License conDefinitionor.
     *
     * @param string $name
     * @param string|null $url
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $name,
        public ?string $url = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'url' => $this->url
        ], DefinitionHelper::isNotNull(...));
    }

}
