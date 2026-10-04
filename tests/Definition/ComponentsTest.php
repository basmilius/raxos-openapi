<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Components, Response, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use function RaxosTests\OpenAPI\documentData;

covers(Components::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Components(responses: ['z' => new Response('Z'), 'a' => new Response('A')], schemas: ['z' => new Schema(type: SchemaType::STRING), 'a' => new Schema(type: SchemaType::INTEGER)])))->toBe(['responses' => ['a' => ['description' => 'A'], 'z' => ['description' => 'Z']], 'schemas' => ['a' => ['type' => 'integer'], 'z' => ['type' => 'string']]]);
    expect(documentData(new Components()))->toBe([]);
});
