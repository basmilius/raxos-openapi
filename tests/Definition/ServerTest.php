<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Server::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Server('https://{region}.example.test', variables: ['region' => new D\ServerVariable('eu', enum: ['eu', 'us'])])))->toBe(['url' => 'https://{region}.example.test', 'variables' => ['region' => ['default' => 'eu', 'enum' => ['eu', 'us']]]]);
    expect(documentData(new D\Server('https://example.test')))->toBe(['url' => 'https://example.test']);
});
