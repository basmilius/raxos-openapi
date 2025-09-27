<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;
use Raxos\Contract\OpenAPI\AttributeInterface;
use Raxos\OpenAPI\Definition\ExternalDocumentation;

/**
 * Class Endpoint
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.7.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Endpoint implements AttributeInterface
{

    /**
     * Endpoint constructor.
     *
     * @param string|null $summary
     * @param string|null $description
     * @param Parameter[]|null $parameters
     * @param string[]|null $tags
     * @param ExternalDocumentation|null $externalDocs
     * @param string|null $operationId
     * @param string[]|null $security
     * @param class-string|null $requestModel
     * @param string|null $requestModelDescription
     * @param bool|null $requestModelRequired
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public ?array $parameters = null,
        public ?array $tags = null,
        public ?ExternalDocumentation $externalDocs = null,
        public ?string $operationId = null,
        public ?array $security = null,
        public ?string $requestModel = null,
        public ?string $requestModelDescription = null,
        public ?bool $requestModelRequired = null
    ) {}

}
