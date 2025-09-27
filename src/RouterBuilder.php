<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use Raxos\Collection\Map;
use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\OpenAPI\OpenAPIExceptionInterface;
use Raxos\Contract\Router\{FrameInterface, RouterInterface};
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{MediaType, Operation, Parameter, Path, RequestBody, Response};
use Raxos\OpenAPI\Enum\In;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Frame\{ControllerFrame, FrameStack, RouteFrame};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use function array_filter;
use function array_find;
use function array_key_first;
use function array_map;
use function array_values;
use function is_subclass_of;
use function iterator_to_array;
use function str_contains;
use function str_replace;
use function strtolower;

/**
 * Class RouterBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.8.0
 */
final class RouterBuilder
{

    public MapInterface $responses {
        get => $this->builder->responses;
    }

    public MapInterface $schemas {
        get => $this->builder->schemas;
    }

    /**
     * RouterBuilder constructor.
     *
     * @param RouterInterface $router
     * @param MapInterface<string, Path> $paths
     * @param SchemaBuilder $builder
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.8.0
     */
    public function __construct(
        public RouterInterface $router,
        public MapInterface $paths = new Map(),
        public SchemaBuilder $builder = new SchemaBuilder()
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

        foreach ($this->router->dynamicRoutes as $route) {
            $this->path($route);
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
    private function operation(FrameStack $stack, array $parameters): ?Operation
    {
        try {
            /** @var RouteFrame|null $frame */
            $frame = array_find($stack->frames, static fn(FrameInterface $frame) => $frame instanceof RouteFrame);

            if ($frame === null) {
                return null;
            }

            $controller = new ReflectionClass($frame->route->class);
            $handler = $controller->getMethod($frame->route->method);

            $hidden = !empty($controller->getAttributes(Attr\Hidden::class)) || !empty($handler->getAttributes(Attr\Hidden::class));
            /** @var ReflectionAttribute<Attr\Endpoint> $endpoint */
            $endpoint = $handler->getAttributes(Attr\Endpoint::class)[0] ?? null;

            if ($hidden || $endpoint === null) {
                return null;
            }

            $endpoint = $endpoint->newInstance();

            $parameters = [
                ...array_filter($parameters, static fn(Parameter $parameter) => $parameter->in !== In::PATH),
                ...array_map(static fn(Attr\Parameter $parameter) => new Parameter(
                    name: $parameter->name,
                    in: $parameter->in,
                    description: $parameter->description,
                    required: $parameter->required,
                    deprecated: $parameter->deprecated,
                    allowEmptyValue: $parameter->allowEmptyValue
                ), $endpoint->parameters ?? []),
            ];

            $responses = array_map(static fn(ReflectionAttribute $attr) => $attr->newInstance(), $handler->getAttributes(Attr\Response::class));
            $responses = iterator_to_array($this->responses($responses));

            $requestBody = null;

            if ($endpoint->requestModel !== null || $endpoint->requestModelDescription !== null || $endpoint->requestModelRequired !== null) {
                $content = null;

                if ($endpoint->requestModel !== null && is_subclass_of($endpoint->requestModel, HttpRequestModelInterface::class)) {
                    $schema = $this->builder->reference($endpoint->requestModel);

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

        $stack = $route[array_key_first($route)];
        $parameters = array_values(iterator_to_array($this->parameters($stack)));
        $path = $stack->pathPlain;

        foreach ($parameters as $parameter) {
            $path = str_replace("\${$parameter->name}", "{{$parameter->name}}", $path);
        }

        foreach ($route as $method => $stack) {
            $operation = $this->operation($stack, $parameters);

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
            parameters: $parameters
        ));
    }

    /**
     * Generates the response definitions.
     *
     * @param Attr\Response[] $responses
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

}
