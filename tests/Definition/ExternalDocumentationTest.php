<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\ExternalDocumentation;
use function RaxosTests\OpenAPI\documentData;

covers(ExternalDocumentation::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new ExternalDocumentation('', 'https://example.test/docs')))->toBe(['description' => '', 'url' => 'https://example.test/docs']);
});
