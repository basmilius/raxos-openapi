<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use Raxos\OpenAPI\Enum as E;
use function RaxosTests\OpenAPI\documentData;

covers(D\Response::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Response('', [], ['application/json' => new D\MediaType(new D\Schema(type: E\SchemaType::BOOLEAN), false)])))->toBe(['description' => '', 'headers' => [], 'content' => ['application/json' => ['schema' => ['type' => 'boolean'], 'example' => false]]]);
});
