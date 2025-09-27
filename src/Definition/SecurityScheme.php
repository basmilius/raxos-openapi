<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\Contract\OpenAPI\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use Raxos\OpenAPI\Enum\{In, SecuritySchemeType, SecurityType};
use function array_filter;

/**
 * Class SecurityScheme
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class SecurityScheme implements DefinitionInterface
{

    /**
     * SecurityScheme conDefinitionor.
     *
     * @param SecurityType $type
     * @param SecuritySchemeType|null $scheme
     * @param In|null $in
     * @param string|null $bearerFormat
     * @param string|null $name
     * @param array|null $flows
     * @param string|null $openIdConnectUrl
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public SecurityType $type,
        public ?SecuritySchemeType $scheme = null,
        public ?In $in = null,
        public ?string $bearerFormat = null,
        public ?string $name = null,
        public ?array $flows = null,
        public ?string $openIdConnectUrl = null,
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'type' => $this->type,
            'scheme' => $this->scheme,
            'in' => $this->in,
            'bearerFormat' => $this->bearerFormat,
            'name' => $this->name,
            'flows' => $this->flows,
            'openIdConnectUrl' => $this->openIdConnectUrl
        ], DefinitionHelper::isNotNull(...));
    }

}
