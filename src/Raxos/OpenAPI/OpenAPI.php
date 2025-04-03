<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use JsonException;
use Raxos\OpenAPI\Contract\DefinitionInterface;
use Raxos\OpenAPI\Definition\{Components, Info, Path, Server, Tag};
use Raxos\Router\Contract\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use function array_filter;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function ksort;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;

/**
 * Class OpenAPI
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.7.0
 */
final readonly class OpenAPI implements DefinitionInterface
{

    public const string SCHEMA = 'https://spec.openapis.org/oas/3.1.1/schema/2022-04-27';
    public const string VERSION = '3.1.1';

    /**
     * OpenAPI constructor.
     *
     * @param Info $info
     * @param Server[] $servers
     * @param Path[] $paths
     * @param Components|null $components
     * @param Tag[] $tags
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public Info $info,
        public array $servers = [],
        public array $paths = [],
        public ?Components $components = null,
        public array $tags = []
    ) {}

    /**
     * Returns the OpenAPI spec as JSON.
     *
     * @return string
     * @throws JsonException
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function getJSON(): string
    {
        return json_encode($this, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
    }

    /**
     * Returns the OpenAPI spec as YAML.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function getYAML(): string
    {
        return Yaml::dump(
            json_decode(json_encode($this), true),
            inline: 99,
            indent: 2,
            flags: Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'openapi' => self::VERSION,
            'info' => $this->info,
            'servers' => $this->servers,
            'paths' => $this->paths,
            'components' => $this->components,
            'tags' => $this->tags
        ], DefinitionHelper::isNotEmpty(...));
    }

    /**
     * Converts the mapping of the router into Open API paths.
     *
     * @param RouterInterface $router
     *
     * @return Path[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function getPathsFromRouter(RouterInterface $router): array
    {
        $paths = iterator_to_array(RouterGenerator::generate($router));

        ksort($paths);

        return $paths;
    }

}
