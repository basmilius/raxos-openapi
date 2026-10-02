<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Encoding::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Encoding('application/json', ['X-Mode' => ['schema' => ['type' => 'string']]], 'form', false, false)))->toBe(['contentType' => 'application/json', 'headers' => ['X-Mode' => ['schema' => ['type' => 'string']]], 'style' => 'form', 'explode' => false, 'allowReserved' => false]);
});
