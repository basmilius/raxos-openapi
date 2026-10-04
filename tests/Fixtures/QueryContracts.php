<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Raxos\OpenAPI\Attribute\Endpoint;
use Raxos\Router\Attribute\{Controller, Get, MapQuery};

#[Controller('/query-contracts')]
final readonly class QueryContractsController
{

    #[Get('/')]
    #[Endpoint]
    public function index(#[MapQuery('page_size')] int $size, #[MapQuery] ?string $term, #[MapQuery(enum: UnitInteger::class)] array $states, #[MapQuery] int $offset = 0): array
    {
        return [];
    }

}
