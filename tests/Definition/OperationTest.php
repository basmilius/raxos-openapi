<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Operation::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Operation(operationId: 'read', tags: [], parameters: [], responses: [200 => new D\Response('OK')], security: [])))->toBe(['operationId' => 'read', 'tags' => [], 'parameters' => [], 'responses' => [200 => ['description' => 'OK']], 'deprecated' => false, 'security' => []]);
});
