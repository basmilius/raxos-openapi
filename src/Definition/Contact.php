<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Contact
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.7.0
 */
final readonly class Contact implements DefinitionInterface
{

    /**
     * Contact constructor.
     *
     * @param string $name
     * @param string|null $email
     * @param string|null $url
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $name,
        public ?string $email = null,
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
            'email' => $this->email,
            'url' => $this->url
        ], DefinitionHelper::isNotNull(...));
    }

}
