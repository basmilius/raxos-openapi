<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Attribute;
use Closure;
use Generator;
use LogicException;
use Raxos\Contract\OpenAPI\ParameterizedMiddlewareInterface;
use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\OpenAPI\Attribute\{Endpoint, FilterParams, Hidden, Parameter as ParameterAttribute, Response};
use Raxos\OpenAPI\Definition\Parameter;
use Raxos\OpenAPI\Enum\In;
use Raxos\Router\Attribute\{Controller, Delete, Get, Head, MapQuery, Options, Patch, Post, Put};
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Filter\{DateTime, Exact, Text};

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class UnitApiMiddleware implements ParameterizedMiddlewareInterface
{

    public function handle(HttpRequest $request, Closure $next): HttpResponse
    {
        return $next($request);
    }

    public static function generateParameters(): Generator
    {
        yield 'auth' => new Parameter('X-Unit', In::HEADER, required: true);
        yield 'session' => new Parameter('session', In::COOKIE);
    }

}

#[Filter('group', new Exact())]
#[Filter('created', new DateTime())]
#[Filter('free', new Text())]
final class UnitFilterModel {}

#[Controller('/units')]
final readonly class UnitApiController
{

    #[Get('/$id/$identifier')]
    #[UnitApiMiddleware]
    #[FilterParams(UnitFilterModel::class)]
    #[Endpoint(summary: 'List', parameters: [new ParameterAttribute('group', In::QUERY)], security: ['bearer', 'oauth' => ['read']])]
    #[Response(HttpResponseCode::OK, 'Found', UnitShape::class)]
    public function get(int $id, string $identifier, #[MapQuery('search')] ?string $q = null): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Post('/$id/$identifier')]
    #[Endpoint(requestModel: UnitRequest::class, requestModelDescription: 'Input', requestModelRequired: true, responses: [new Response(HttpResponseCode::CREATED, 'Created')])]
    public function post(int $id, string $identifier): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Put('/static')]
    #[Endpoint(requestModelDescription: 'Optional', requestModelRequired: false)]
    public function put(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Patch('/static')]
    #[Endpoint]
    public function patch(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Delete('/static')]
    #[Endpoint]
    public function delete(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Options('/static')]
    #[Endpoint]
    public function options(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

    #[Head('/static')]
    #[Endpoint]
    public function head(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

}

#[Hidden]
#[Controller('/secret')]
final class UnitHiddenController
{

    #[Get]
    #[Endpoint]
    public function get(): HttpResponse
    {
        throw new LogicException('Must not execute.');
    }

}
