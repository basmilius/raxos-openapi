<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Tag::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Tag('products', '')))->toBe(['name' => 'products', 'description' => '']);
});
