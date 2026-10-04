<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\FloatSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(FloatSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new FloatSchemaBuilder();
    expect($builder::can(['float']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new Property(), ['float'], $nullable)))->toBe(['type' => $nullable ? ['number', 'null'] : 'number', 'format' => 'float']);
})->with([false, true]);
