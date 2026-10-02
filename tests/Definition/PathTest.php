<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Path::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Path(get: new D\Operation(operationId: 'get'), post: new D\Operation(operationId: 'post'))))->toBe(['get' => ['operationId' => 'get', 'deprecated' => false], 'post' => ['operationId' => 'post', 'deprecated' => false]]);
    expect(documentData(new D\Path()))->toBe([]);
});
