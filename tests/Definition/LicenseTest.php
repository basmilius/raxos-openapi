<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\License::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\License('MIT', 'https://example.test/license')))->toBe(['name' => 'MIT', 'url' => 'https://example.test/license']);
    expect(documentData(new D\License('MIT')))->toBe(['name' => 'MIT']);
});
