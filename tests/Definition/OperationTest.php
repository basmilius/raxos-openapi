<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Operation, Response};
use function RaxosTests\OpenAPI\documentData;

covers(Operation::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Operation(operationId: 'read', tags: [], parameters: [], responses: [200 => new Response('OK')], security: [])))->toBe(['operationId' => 'read', 'tags' => [], 'parameters' => [], 'responses' => [200 => ['description' => 'OK']], 'deprecated' => false, 'security' => []]);
});
