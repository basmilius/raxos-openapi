<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Attribute;
use Closure;
use Generator;
use Raxos\Contract\OpenAPI\ParameterizedMiddlewareInterface;
use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\OpenAPI\Attribute as API;
use Raxos\OpenAPI\Definition\Parameter;
use Raxos\OpenAPI\Enum\In;
use Raxos\Router\Attribute as Route;
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

#[Route\Controller('/units')]
final readonly class UnitApiController
{
    #[Route\Get('/$id/$identifier')]
    #[UnitApiMiddleware]
    #[API\FilterParams(UnitFilterModel::class)]
    #[API\Endpoint(summary: 'List', parameters: [new API\Parameter('group', In::QUERY)], security: ['bearer', 'oauth' => ['read']])]
    #[API\Response(HttpResponseCode::OK, 'Found', UnitShape::class)]
    public function get(int $id, string $identifier, #[Route\MapQuery('search')] ?string $q = null): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Post('/$id/$identifier')]
    #[API\Endpoint(requestModel: UnitRequest::class, requestModelDescription: 'Input', requestModelRequired: true, responses: [new API\Response(HttpResponseCode::CREATED, 'Created')])]
    public function post(int $id, string $identifier): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Put('/static')]
    #[API\Endpoint(requestModelDescription: 'Optional', requestModelRequired: false)]
    public function put(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Patch('/static')]
    #[API\Endpoint]
    public function patch(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Delete('/static')]
    #[API\Endpoint]
    public function delete(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Options('/static')]
    #[API\Endpoint]
    public function options(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }

    #[Route\Head('/static')]
    #[API\Endpoint]
    public function head(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }
}

#[API\Hidden]
#[Route\Controller('/secret')]
final class UnitHiddenController
{
    #[Route\Get]
    #[API\Endpoint]
    public function get(): HttpResponse
    {
        throw new \LogicException('Must not execute.');
    }
}
