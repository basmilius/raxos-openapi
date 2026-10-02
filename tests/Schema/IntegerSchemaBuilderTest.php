<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(B\IntegerSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new B\IntegerSchemaBuilder();
    expect($builder::can(['int']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new A\Property(), ['int'], $nullable)))->toBe(['type' => $nullable ? ['integer', 'null'] : 'integer', 'format' => 'int32']);
})->with([false, true]);
