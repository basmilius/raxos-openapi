<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\StringSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\UnitStringable;
use function RaxosTests\OpenAPI\documentData;

covers(StringSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new StringSchemaBuilder();
    expect($builder::can(['string']))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new Property(), ['string'], $nullable)))->toBe(['type' => $nullable ? ['string', 'null'] : 'string']);
})->with([false, true]);

it('accepts stringable DTOs as strings', function (): void {
    expect(StringSchemaBuilder::can([UnitStringable::class]))->toBeTrue();
});
