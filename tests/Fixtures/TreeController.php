<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Tests\Fixtures;

use Raxos\Http\HttpResponse;
use Raxos\Http\HttpResponseCode;
use Raxos\OpenAPI\Attribute as API;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\Attribute\Get;
use RuntimeException;

#[Controller('/trees')]
final readonly class TreeController
{

    #[Get('/$id')]
    #[API\Endpoint(summary: 'Read a tree', operationId: 'getTree')]
    #[API\Response(HttpResponseCode::OK, description: 'A tree', model: JsonTree::class)]
    public function get(int $id): HttpResponse
    {
        throw new RuntimeException('Documentation must not execute the handler.');
    }

    #[Get('/hidden')]
    #[API\Endpoint]
    #[API\Hidden]
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
