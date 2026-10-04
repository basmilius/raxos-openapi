<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Server, ServerVariable};
use function RaxosTests\OpenAPI\documentData;

covers(Server::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Server('https://{region}.example.test', variables: ['region' => new ServerVariable('eu', enum: ['eu', 'us'])])))->toBe(['url' => 'https://{region}.example.test', 'variables' => ['region' => ['default' => 'eu', 'enum' => ['eu', 'us']]]]);
    expect(documentData(new Server('https://example.test')))->toBe(['url' => 'https://example.test']);
});
