<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(B\FloatSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new B\FloatSchemaBuilder();
    expect($builder::can(['float']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new A\Property(), ['float'], $nullable)))->toBe(['type' => $nullable ? ['number', 'null'] : 'number', 'format' => 'float']);
})->with([false, true]);
