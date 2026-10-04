<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use Raxos\Collection\Map;
use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\OpenAPI\{OpenAPIExceptionInterface, ParameterizedMiddlewareInterface};
use Raxos\Contract\Router\{FrameInterface, RouterInterface};
use Raxos\Contract\Search\StructuredFilterInterface;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\OpenAPI\Attribute\{Endpoint, FilterParams, Hidden, Parameter as ParameterAttribute, Property, Response as ResponseAttribute};
use Raxos\OpenAPI\Definition\{MediaType, Operation, Parameter, Path, RequestBody, Response, Schema};
use Raxos\OpenAPI\Enum\{In, SchemaType, StringFormat};
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\Router\Attribute\MapQuery;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Frame\{ControllerFrame, FrameStack, RouteFrame};
use Raxos\Search\Attribute\Filter as SearchFilter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use function array_filter;
use function array_find;
use function array_first;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_values;
use function in_array;
use function is_subclass_of;
use function iterator_to_array;
use function str_contains;
use function str_replace;
use function strcmp;
use function strlen;
use function strtolower;
use function usort;

/**
 * Class RouterBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.8.0
 */
final class RouterBuilder
{

    /**
     * Reuses generated response components while building the route specification.
     *
     * @var MapInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public MapInterface $responses {
        get => $this->builder->responses;
    }

    /**
     * Reuses the component registry while deriving route request and response schemas.
     *
     * @var MapInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public MapInterface $schemas {
        get => $this->builder->schemas;
    }

    /**
     * RouterBuilder constructor.
     *
     * @param RouterInterface $router
     * @param MapInterface<string, Path> $paths
     * @param SchemaBuilder $builder
     * @param class-string[]|null $controllers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public RouterInterface $router,
        public MapInterface $paths = new Map(),
        public SchemaBuilder $builder = new SchemaBuilder(),
        public ?array $controllers = null
    ) {}

    /**
     * Builds paths from the router.
     *
     * @return void
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function build(): void
    {
        foreach ($this->router->staticRoutes as $route) {
            $this->path($route);
        }

        foreach ($this->router->dynamicRoutes as $routes) {
            foreach ($routes as $route) {
                $this->path($route);
            }
        }
    }

    /**
     * Returns an operation based on a route frame.
     *
     * @param FrameStack $stack
     * @param array $parameters
     *
     * @return Operation|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function operation(
        FrameStack $stack,
        array $parameters
    ): ?Operation
    {
        try {
            /** @var RouteFrame|null $frame */
            $frame = array_find($stack->frames, static fn(FrameInterface $frame) => $frame instanceof RouteFrame);

            if ($frame === null) {
                return null;
            }

            if ($this->controllers !== null && !in_array($frame->route->class, $this->controllers, true)) {
                return null;
            }

            $controller = new ReflectionClass($frame->route->class);
            $handler = $controller->getMethod($frame->route->method);

            $hidden = !empty($controller->getAttributes(Hidden::class)) || !empty($handler->getAttributes(Hidden::class));
            /** @var ReflectionAttribute<Endpoint> $endpoint */
            $endpoint = $handler->getAttributes(Endpoint::class)[0] ?? null;

            if ($hidden || $endpoint === null) {
                return null;
            }

            $endpoint = $endpoint->newInstance();

            $parameters = [
                ...array_filter($parameters, static fn(Parameter $parameter) => $parameter->in !== In::PATH),
                ...array_map(static fn(ParameterAttribute $parameter) => new Parameter(
                    name: $parameter->name,
                    in: $parameter->in,
                    description: $parameter->description,
                    required: $parameter->required,
                    deprecated: $parameter->deprecated,
                    allowEmptyValue: $parameter->allowEmptyValue
                ), $endpoint->parameters ?? []),
            ];

            foreach ($handler->getParameters() as $parameter) {
                $mapQuery = $parameter->getAttributes(MapQuery::class)[0] ?? null;

                if ($mapQuery === null) {
                    continue;
                }

                $queryAttribute = $mapQuery->newInstance();
                $name = $queryAttribute->key ?? $parameter->name;
                $existing = array_filter($parameters, static fn(Parameter $item): bool => $item->in === In::QUERY && $item->name === $name);

                if ($existing !== []) {
                    continue;
                }

                $types = ReflectionUtil::getTypes($parameter->getType());
                $schema = $this->builder->auto(new Property(), $types, $parameter->getType()?->allowsNull() ?? false);

                if ($queryAttribute->enum !== null && in_array('array', $types, true)) {
                    $schema = new Schema(type: SchemaType::ARRAY, items: $this->builder->auto(new Property(), [$queryAttribute->enum]));
                }

                if ($parameter->isDefaultValueAvailable() && $schema instanceof Schema) {
                    $values = get_object_vars($schema);
                    $values['default'] = $parameter->getDefaultValue();
                    $schema = new Schema(...$values);
                }

                $parameters[] = new Parameter(
                    name: $name,
                    in: In::QUERY,
                    required: !$parameter->isDefaultValueAvailable() && !($parameter->getType()?->allowsNull() ?? false) && !in_array('array', $types, true),
                    schema: $schema
                );
            }

            $filterParams = $handler->getAttributes(FilterParams::class)[0] ?? null;

            if ($filterParams !== null) {
                $model = $filterParams->newInstance()->model;
                $existing = array_map(
                    static fn(Parameter $parameter) => $parameter->name,
                    array_filter($parameters, static fn(Parameter $parameter) => $parameter->in === In::QUERY)
                );

                foreach (new ReflectionClass($model)->getAttributes(SearchFilter::class) as $filterAttribute) {
                    $filterAttribute = $filterAttribute->newInstance();
                    $filter = $filterAttribute->filter;

                    if (!($filter instanceof StructuredFilterInterface)) {
                        continue;
                    }

                    foreach ($filter->describe($filterAttribute->property) as $spec) {
                        if (in_array($spec['name'], $existing, true)) {
                            continue;
                        }

                        $existing[] = $spec['name'];
                        $parameters[] = new Parameter(
                            name: $spec['name'],
                            in: In::QUERY,
                            required: false,
                            schema: new Schema(
                                type: SchemaType::from($spec['type']),
                                format: isset($spec['format']) ? StringFormat::from($spec['format']) : null,
                                enum: $spec['enum'] ?? null
                            )
                        );
                    }
                }
            }

            $responses = array_map(static fn(ReflectionAttribute $attr) => $attr->newInstance(), $handler->getAttributes(ResponseAttribute::class));

            if ($endpoint->responses !== null) {
                $responses = array_merge($responses, $endpoint->responses);
            }

            $responses = iterator_to_array($this->responses($responses));

            $requestBody = null;

            if ($endpoint->requestModel !== null || $endpoint->requestModelDescription !== null || $endpoint->requestModelRequired !== null) {
                $content = null;

                if ($endpoint->requestModel !== null && is_subclass_of($endpoint->requestModel, HttpRequestModelInterface::class)) {
                    $schema = $this->builder->requestReference($endpoint->requestModel);

                    if ($schema !== null) {
                        $content = [];
                        $content['application/json'] = new MediaType($schema);
                    }
                }

                $requestBody = new RequestBody(
                    description: $endpoint->requestModelDescription,
                    content: $content,
                    required: $endpoint->requestModelRequired
                );
            }

            return new Operation(
                summary: $endpoint->summary,
                description: $endpoint->description,
                externalDocs: $endpoint->externalDocs,
                operationId: $endpoint->operationId,
                tags: $endpoint->tags ?? [],
                parameters: array_values($parameters),
                requestBody: $requestBody,
                responses: $responses,
                security: $endpoint->security !== null ? iterator_to_array(DefinitionHelper::normalizeSecurity($endpoint->security)) : null
            );
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($err);
        }
    }

    /**
     * Returns a parameter definition based on a route injectable.
     *
     * @param Injectable $injectable
     *
     * @return Parameter
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function parameter(Injectable $injectable): Parameter
    {
        return new Parameter(
            $injectable->name,
            In::PATH,
            required: true
        );
    }

    /**
     * Generates parameter definitions from the parameters in the route.
     *
     * @param FrameStack $stack
     *
     * @return Generator<Parameter>
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function parameters(FrameStack $stack): Generator
    {
        foreach ($stack->frames as $frame) {
            $parameters = match (true) {
                $frame instanceof ControllerFrame => $frame->controller->parameters,
                $frame instanceof RouteFrame => $frame->route->parameters,
                default => []
            };

            if ($frame instanceof RouteFrame) {
                foreach ($frame->route->middlewares as $middleware) {
                    if (!is_subclass_of($middleware->class, ParameterizedMiddlewareInterface::class)) {
                        continue;
                    }

                    yield from $middleware->class::generateParameters();
                }
            }

            if (empty($parameters)) {
                continue;
            }

            foreach ($parameters as $parameter) {
                if (!str_contains($stack->pathPlain, "\${$parameter->name}")) {
                    continue;
                }

                yield $parameter->name => $this->parameter($parameter);
            }
        }
    }

    /**
     * Adds a path definition based on the route.
     *
     * @param array $route
     *
     * @return void
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function path(array $route): void
    {
        $operations = [];

        if (array_key_exists('segments', $route)) {
            unset($route['segments']);
        }

        $stack = array_first($route);
        /** @var Parameter[] $parameters */
        $parameters = array_values(iterator_to_array($this->parameters($stack)));
        $pathParameters = array_filter($parameters, static fn(Parameter $parameter) => $parameter->in === In::PATH);

        usort($parameters, fn(Parameter $a, Parameter $b) => ($this->sortByIn($a->in) <=> $this->sortByIn($b->in)) ?: strcmp($a->name, $b->name));
        usort($pathParameters, static fn(Parameter $a, Parameter $b) => strlen($b->name) <=> strlen($a->name));

        $path = $stack->pathPlain;

        foreach ($pathParameters as $parameter) {
            $path = str_replace("\${$parameter->name}", "{{$parameter->name}}", $path);
        }

        foreach ($route as $method => $stack) {
            $operationParameters = array_values(iterator_to_array($this->parameters($stack)));
            usort($operationParameters, fn(Parameter $a, Parameter $b): int => ($this->sortByIn($a->in) <=> $this->sortByIn($b->in)) ?: strcmp($a->name, $b->name));
            $operation = $this->operation($stack, $operationParameters);

            if ($operation === null) {
                continue;
            }

            $operations[strtolower($method)] = $operation;
        }

        if (empty($operations)) {
            return;
        }

        $this->paths->set($path, new Path(
            get: $operations['get'] ?? null,
            put: $operations['put'] ?? null,
            post: $operations['post'] ?? null,
            delete: $operations['delete'] ?? null,
            options: $operations['options'] ?? null,
            head: $operations['head'] ?? null,
            patch: $operations['patch'] ?? null,
            trace: $operations['trace'] ?? null,
            parameters: array_values(array_filter($parameters, static fn(Parameter $parameter): bool => $parameter->in === In::PATH))
        ));
    }

    /**
     * Generates the response definitions.
     *
     * @param ResponseAttribute[] $responses
     *
     * @return Generator<int, Response>
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    private function responses(array $responses): Generator
    {
        foreach ($responses as $response) {
            yield $response->code->value => $this->builder->response($response);
        }
    }

    /**
     * Returns the sorting order for the In enum.
     *
     * @param In $in
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    private function sortByIn(In $in): int
    {
        return match ($in) {
            In::PATH => 0,
            In::QUERY => 1,
            In::HEADER => 2,
            In::COOKIE => 3,
        };
    }

}
