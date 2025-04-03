<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Tag
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Tag implements DefinitionInterface
{

    /**
     * Tag conDefinitionor.
     *
     * @param string $name
     * @param string $description
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $name,
        public string $description
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
            'description' => $this->description
        ], DefinitionHelper::isNotNull(...));
    }

}
