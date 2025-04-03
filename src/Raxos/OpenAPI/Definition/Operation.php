<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Operation
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Operation implements DefinitionInterface
{

    /**
     * Operation conDefinitionor.
     *
     * @param string|null $summary
     * @param string|null $description
     * @param ExternalDocumentation|null $externalDocs
     * @param string|null $operationId
     * @param string[]|null $tags
     * @param Parameter[]|null $parameters
     * @param mixed|null $requestBody
     * @param array|null $responses
     * @param bool $deprecated
     * @param array|null $security
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public ?ExternalDocumentation $externalDocs = null,
        public ?string $operationId = null,
        public ?array $tags = null,
        public ?array $parameters = null,
        public mixed $requestBody = null,
        public ?array $responses = null,
        public bool $deprecated = false,
        public ?array $security = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'summary' => $this->summary,
            'description' => $this->description,
            'externalDocs' => $this->externalDocs,
            'operationId' => $this->operationId,
            'tags' => $this->tags,
            'parameters' => $this->parameters,
            'requestBody' => $this->requestBody,
            'responses' => $this->responses,
            'deprecated' => $this->deprecated,
            'security' => $this->security
        ], DefinitionHelper::isNotEmpty(...));
    }

}
