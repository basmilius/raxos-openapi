<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Tests\Fixtures;

use Raxos\Http\{HttpResponse, HttpResponseCode};
use Raxos\OpenAPI\Attribute\{Endpoint, Hidden, Response};
use Raxos\Router\Attribute\{Controller, Get};
use RuntimeException;

#[Controller('/trees')]
final readonly class TreeController
{

    #[Get('/$id')]
    #[Endpoint(summary: 'Read a tree', operationId: 'getTree')]
    #[Response(HttpResponseCode::OK, description: 'A tree', model: JsonTree::class)]
    public function get(int $id): HttpResponse
    {
        throw new RuntimeException('Documentation must not execute the handler.');
    }

    #[Get('/hidden')]
    #[Endpoint]
    #[Hidden]
    public function hidden(): HttpResponse
    {
        throw new RuntimeException('Documentation must not execute the handler.');
    }

    #[Get('/undocumented')]
    public function undocumented(): HttpResponse
    {
        throw new RuntimeException('Documentation must not execute the handler.');
    }

}
