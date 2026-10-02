<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use Raxos\OpenAPI\Enum as E;
use function RaxosTests\OpenAPI\documentData;

covers(D\Components::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Components(responses: ['z' => new D\Response('Z'), 'a' => new D\Response('A')], schemas: ['z' => new D\Schema(type: E\SchemaType::STRING), 'a' => new D\Schema(type: E\SchemaType::INTEGER)])))->toBe(['responses' => ['a' => ['description' => 'A'], 'z' => ['description' => 'Z']], 'schemas' => ['a' => ['type' => 'integer'], 'z' => ['type' => 'string']]]);
    expect(documentData(new D\Components()))->toBe([]);
});
