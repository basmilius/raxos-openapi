<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\EnumSchemaBuilder::class);

it('retains numeric, string and empty backed enums with nullable values', function (string $class, string $type, array $values): void {
    $builder = new B\EnumSchemaBuilder();
    expect($builder::can([$class]))->toBeTrue()->and($builder::can(['string']))->toBeFalse();
    expect(documentData($builder->build(new SchemaBuilder(), new A\Property(), [$class], false)))->toBe(['type' => $type, 'enum' => $values]);
    expect(documentData($builder->build(new SchemaBuilder(), new A\Property(), [$class], true)))->toBe(['type' => [$type, 'null'], 'enum' => [...$values, null]]);
})->with([
    [F\UnitInteger::class, 'integer', [0, 1]], [F\UnitEmptyInteger::class, 'integer', []],
    [F\UnitEmptyString::class, 'string', []], [Raxos\OpenAPI\Tests\Fixtures\State::class, 'string', ['ready']]
]);
