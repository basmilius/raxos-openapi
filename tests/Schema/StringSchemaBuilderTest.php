<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\StringSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new B\StringSchemaBuilder();
    expect($builder::can(['string']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new A\Property(), ['string'], $nullable)))->toBe(['type' => $nullable ? ['string', 'null'] : 'string']);
})->with([false, true]);

it('accepts stringable DTOs as strings', function (): void {
    expect(B\StringSchemaBuilder::can([F\UnitStringable::class]))->toBeTrue();
});
