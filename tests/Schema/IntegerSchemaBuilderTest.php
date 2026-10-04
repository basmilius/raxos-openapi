<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\IntegerSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(IntegerSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new IntegerSchemaBuilder();
    expect($builder::can(['int']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new Property(), ['int'], $nullable)))->toBe(['type' => $nullable ? ['integer', 'null'] : 'integer', 'format' => 'int32']);
})->with([false, true]);
