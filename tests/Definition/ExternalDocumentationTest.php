<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\ExternalDocumentation::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\ExternalDocumentation('', 'https://example.test/docs')))->toBe(['description' => '', 'url' => 'https://example.test/docs']);
});
