<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Definition;

use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\DefinitionHelper;
use function array_filter;

/**
 * Class Path
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
final readonly class Path implements DefinitionInterface
{

    /**
     * Path conDefinitionor.
     *
     * @param string|null $summary
     * @param string|null $description
     * @param Operation|null $get
     * @param Operation|null $put
     * @param Operation|null $post
     * @param Operation|null $delete
     * @param Operation|null $options
     * @param Operation|null $head
     * @param Operation|null $patch
     * @param Operation|null $trace
     * @param Server[]|null $servers
     * @param Parameter[]|null $parameters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public ?Operation $get = null,
        public ?Operation $put = null,
        public ?Operation $post = null,
        public ?Operation $delete = null,
        public ?Operation $options = null,
        public ?Operation $head = null,
        public ?Operation $patch = null,
        public ?Operation $trace = null,
        public ?array $servers = null,
        public ?array $parameters = null
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
            'get' => $this->get,
            'put' => $this->put,
            'post' => $this->post,
            'delete' => $this->delete,
            'options' => $this->options,
            'head' => $this->head,
            'patch' => $this->patch,
            'trace' => $this->trace,
            'servers' => $this->servers,
            'parameters' => $this->parameters
        ], DefinitionHelper::isNotEmpty(...));
    }

}
