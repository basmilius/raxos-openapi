<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\JsonSchemaBuilder::class);

it('interprets optional and nested ArrayShape types without executing serialization', function (): void {
    $builder = new B\JsonSchemaBuilder();
    expect($builder::can([F\UnitShape::class]))->toBeTrue()->and($builder::can([F\UnitJsonWithoutShape::class]))->toBeFalse()
        ->and($builder::can([F\UnitDto::class]))->toBeFalse();
    $properties = documentData($builder->build(new SchemaBuilder(), new A\Model(), [F\UnitShape::class], false))['properties'];
    expect(array_keys($properties))->toBe(['optional', 'list', 'nested'])
        ->and($properties['optional']['anyOf'])->toBe([['type' => 'string'], ['type' => 'null']])
        ->and($properties['list'])->toBe(['type' => 'array', 'items' => ['type' => 'integer', 'format' => 'int32']])
        ->and($properties['nested'])->toBe(['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['anyOf' => [['type' => 'boolean'], ['type' => 'null']]]]]);
    expect(documentData($builder->build(new SchemaBuilder(), new A\Model(), [F\UnitJsonWithoutShape::class], false)))->toBe(['type' => 'object']);
    expect(fn() => $builder->build(new SchemaBuilder(), new A\Model(), ['missing-class'], false))->toThrow(Raxos\OpenAPI\Error\ReflectionErrorException::class);
});
