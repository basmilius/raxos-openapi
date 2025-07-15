<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Example
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class Example implements DefinitionInterface
{

    /**
     * Example constructor.
     *
     * @param string $summary
     * @param mixed $value
     * @param string|null $description
     * @param string|null $externalValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public string $summary,
        public mixed $value,
        public ?string $description = null,
        public ?string $externalValue = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'summary' => $this->summary,
            'value' => $this->value,
            'description' => $this->description,
            'externalValue' => $this->externalValue,
        ], DefinitionHelper::isNotEmpty(...));
    }

}
