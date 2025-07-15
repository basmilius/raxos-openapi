<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Encoding
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class Encoding implements DefinitionInterface
{

    /**
     * Encoding constructor.
     *
     * @param string|null $contentType
     * @param array|null $headers
     * @param string|null $style
     * @param bool|null $explode
     * @param bool|null $allowReserved
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public ?string $contentType = null,
        public ?array $headers = null,
        public ?string $style = null,
        public ?bool $explode = null,
        public ?bool $allowReserved = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'contentType' => $this->contentType,
            'headers' => $this->headers,
            'style' => $this->style,
            'explode' => $this->explode,
            'allowReserved' => $this->allowReserved
        ], DefinitionHelper::isNotEmpty(...));
    }

}
