<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\EnumSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use Raxos\OpenAPI\Tests\Fixtures\State;
use RaxosTests\OpenAPI\{UnitEmptyInteger, UnitEmptyString, UnitInteger};
use function RaxosTests\OpenAPI\documentData;

covers(EnumSchemaBuilder::class);

it('retains numeric, string and empty backed enums with nullable values', function (string $class, string $type, array $values): void {
    $builder = new EnumSchemaBuilder();
    expect($builder::can([$class]))->toBeTrue()->and($builder::can(['string']))->toBeFalse();
    expect(documentData($builder->build(new SchemaBuilder(), new Property(), [$class], false)))->toBe(['type' => $type, 'enum' => $values]);
    expect(documentData($builder->build(new SchemaBuilder(), new Property(), [$class], true)))->toBe(['type' => [$type, 'null'], 'enum' => [...$values, null]]);
})->with([
    [UnitInteger::class, 'integer', [0, 1]], [UnitEmptyInteger::class, 'integer', []],
    [UnitEmptyString::class, 'string', []], [State::class, 'string', ['ready']]
]);
