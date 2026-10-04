<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Operation, Path};
use function RaxosTests\OpenAPI\documentData;

covers(Path::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Path(get: new Operation(operationId: 'get'), post: new Operation(operationId: 'post'))))->toBe(['get' => ['operationId' => 'get', 'deprecated' => false], 'post' => ['operationId' => 'post', 'deprecated' => false]]);
    expect(documentData(new Path()))->toBe([]);
});
