<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\Reference;
use function RaxosTests\OpenAPI\documentData;

covers(Reference::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Reference('#/components/schemas/Tree')))->toBe(['$ref' => '#/components/schemas/Tree']);
});
