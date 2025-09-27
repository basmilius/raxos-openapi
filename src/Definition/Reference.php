<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;

/**
 * Class Reference
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Definition
 * @since 1.8.0
 */
final readonly class Reference implements DefinitionInterface
{

    /**
     * Reference constructor.
     *
     * @param string $to
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public string $to
    ) {}

    /**
     *
     *
     * @return string[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function jsonSerialize(): array
    {
        return [
            '$ref' => $this->to
        ];
    }

}
