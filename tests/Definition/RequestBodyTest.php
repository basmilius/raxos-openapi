<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\RequestBody;
use function RaxosTests\OpenAPI\documentData;

covers(RequestBody::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new RequestBody('', [], false)))->toBe(['description' => '', 'content' => [], 'required' => false]);
});
