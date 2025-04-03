<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\{Operation, Parameter, Path, Response};
use Raxos\OpenAPI\Enum\In;
use Raxos\Router\Contract\{FrameInterface, RouterInterface};
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
use function iterator_to_array;
use function str_contains;
use function str_replace;
use function strtolower;

/**
 * Class RouterGenerator
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.7.0
 */
final class RouterGenerator
{

    /**
     * Generates path definitions from the mapping of the given router.
     *
     * @param RouterInterface $router
     *
     * @return Generator<string, Path>
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function generate(RouterInterface $router): Generator
    {
        foreach ($router->staticRoutes as $route) {
            yield from self::path($route);
        }

        foreach ($router->dynamicRoutes as $route) {
            yield from self::path($route);
        }
    }

    /**
     * Returns an operation based on the given frame stack and parameters.
     *
     * @param FrameStack $stack
     * @param Parameter[] $parameters
     *
     * @return Operation|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function operation(FrameStack $stack, array $parameters): ?Operation
    {
        /** @var RouteFrame|null $route */
        $route = array_find($stack->frames, static fn(FrameInterface $frame) => $frame instanceof RouteFrame);

        if ($route === null) {
            return null;
        }

        $controller = new ReflectionClass($route->route->class);
        $handler = $controller->getMethod($route->route->method);

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
        $responses = iterator_to_array(self::responses($responses));

        return new Operation(
            summary: $endpoint->summary ?? null,
            description: $endpoint->description ?? null,
            externalDocs: $endpoint->externalDocs ?? null,
            operationId: $endpoint->operationId ?? null,
            tags: $endpoint->tags ?? [],
            parameters: array_values($parameters),
            responses: $responses,
            security: $endpoint->security !== null ? iterator_to_array(DefinitionHelper::normalizeSecurity($endpoint->security)) : null
        );
    }

    /**
     * Returns a parameter definition based on the given injectable.
     *
     * @param Injectable $injectable
     *
     * @return Parameter
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function parameter(Injectable $injectable): Parameter
    {
        return new Parameter(
            $injectable->name,
            In::PATH,
            required: true
        );
    }

    /**
     * Generates parameter definitions from the parameters and injectables of
     * the given frame stack.
     *
     * @param FrameStack $stack
     *
     * @return Generator<string, Parameter>
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function parameters(FrameStack $stack): Generator
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

                yield $parameter->name => self::parameter($parameter);
            }
        }
    }

    /**
     * Generates a path definition based on the given route.
     *
     * @param array $route
     *
     * @return Generator<string, Path>
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function path(array $route): Generator
    {
        $operations = [];

        $stack = $route[array_key_first($route)];
        $parameters = array_values(iterator_to_array(self::parameters($stack)));
        $path = $stack->pathPlain;

        foreach ($parameters as $parameter) {
            $path = str_replace("\${$parameter->name}", "{{$parameter->name}}", $path);
        }

        foreach ($route as $method => $stack) {
            $operation = self::operation($stack, $parameters);

            if ($operation === null) {
                continue;
            }

            $operations[strtolower($method)] = $operation;
        }

        if (empty($operations)) {
            return;
        }

        yield $path => new Path(
            get: $operations['get'] ?? null,
            put: $operations['put'] ?? null,
            post: $operations['post'] ?? null,
            delete: $operations['delete'] ?? null,
            options: $operations['options'] ?? null,
            head: $operations['head'] ?? null,
            patch: $operations['patch'] ?? null,
            trace: $operations['trace'] ?? null,
            parameters: $parameters
        );
    }

    /**
     * Returns a response definition.
     *
     * @param Attr\Response $response
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function response(Attr\Response $response): Response
    {
        return new Response(
            description: $response->description
        );
    }

    /**
     * Generates the response definitions.
     *
     * @param Attr\Response[] $responses
     *
     * @return Generator<int, Response>
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function responses(array $responses): Generator
    {
        foreach ($responses as $response) {
            yield $response->code->value => self::response($response);
        }
    }

}
