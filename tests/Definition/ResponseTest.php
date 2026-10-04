<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{MediaType, Response, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use function RaxosTests\OpenAPI\documentData;

covers(Response::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Response('', [], ['application/json' => new MediaType(new Schema(type: SchemaType::BOOLEAN), false)])))->toBe(['description' => '', 'headers' => [], 'content' => ['application/json' => ['schema' => ['type' => 'boolean'], 'example' => false]]]);
});
