<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\Tag;
use function RaxosTests\OpenAPI\documentData;

covers(Tag::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Tag('products', '')))->toBe(['name' => 'products', 'description' => '']);
});
