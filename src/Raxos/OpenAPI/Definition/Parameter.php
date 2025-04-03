<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use Raxos\OpenAPI\Enum\In;
use function array_filter;

/**
 * Class Parameter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Parameter implements DefinitionInterface
{

    /**
     * Parameter conDefinitionor.
     *
     * @param string $name
     * @param In $in
     * @param string|null $description
     * @param bool $required
     * @param bool $deprecated
     * @param bool $allowEmptyValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $name,
        public In $in,
        public ?string $description = null,
        public bool $required = false,
        public bool $deprecated = false,
        public bool $allowEmptyValue = false
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
            'in' => $this->in,
            'description' => $this->description,
            'required' => $this->required,
            'deprecated' => $this->deprecated,
            'allowEmptyValue' => $this->allowEmptyValue
        ], DefinitionHelper::isNotEmpty(...));
    }

}
