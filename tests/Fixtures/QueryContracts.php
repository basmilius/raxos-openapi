<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Raxos\OpenAPI\Attribute as API;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\Attribute\Get;
use Raxos\Router\Attribute\MapQuery;

#[Controller('/query-contracts')]
final readonly class QueryContractsController
{
    #[Get('/')]
    #[API\Endpoint]
    public function index(#[MapQuery('page_size')] int $size, #[MapQuery] ?string $term, #[MapQuery(enum: UnitInteger::class)] array $states, #[MapQuery] int $offset = 0): array
    {
        return [];
    }
}
