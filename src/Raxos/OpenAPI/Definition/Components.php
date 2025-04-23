<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;
use function ksort;

/**
 * Class Components
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Components implements DefinitionInterface
{

    public ?array $responses;
    public ?array $schemas;

    /**
     * Components conDefinitionor.
     *
     * @param array<string, Response>|null $responses
     * @param array<string, Schema>|null $schemas
     * @param array<string, SecurityScheme>|null $securitySchemes
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        ?array $responses = null,
        ?array $schemas = null,
        public ?array $securitySchemes = null
    )
    {
        if ($responses !== null) {
            ksort($responses);
            $this->responses = $responses;
        }

        if ($schemas !== null) {
            ksort($schemas);
            $this->schemas = $schemas;
        }
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'responses' => $this->responses,
            'schemas' => $this->schemas,
            'securitySchemes' => $this->securitySchemes
        ], DefinitionHelper::isNotNull(...));
    }

}
