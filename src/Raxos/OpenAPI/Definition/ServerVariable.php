<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class ServerVariable
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class ServerVariable implements DefinitionInterface
{

    /**
     * ServerVariable conDefinitionor.
     *
     * @param string|int|null $default
     * @param string|null $description
     * @param array|null $enum
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string|int|null $default = null,
        public ?string $description = null,
        public ?array $enum = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'default' => $this->default,
            'description' => $this->description,
            'enum' => $this->enum
        ], DefinitionHelper::isNotNull(...));
    }

}
