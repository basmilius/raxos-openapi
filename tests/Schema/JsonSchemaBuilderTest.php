<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Model;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\OpenAPI\Schema\JsonSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\{UnitDto, UnitJsonWithoutShape, UnitShape};
use function RaxosTests\OpenAPI\documentData;

covers(JsonSchemaBuilder::class);

it('interprets optional and nested ArrayShape types without executing serialization', function (): void {
    $builder = new JsonSchemaBuilder();
    expect($builder::can([UnitShape::class]))->toBeTrue()->and($builder::can([UnitJsonWithoutShape::class]))->toBeFalse()
        ->and($builder::can([UnitDto::class]))->toBeFalse();
    $properties = documentData($builder->build(new SchemaBuilder(), new Model(), [UnitShape::class], false))['properties'];
    expect(array_keys($properties))->toBe(['optional', 'list', 'nested'])
        ->and($properties['optional']['anyOf'])->toBe([['type' => 'string'], ['type' => 'null']])
        ->and($properties['list'])->toBe(['type' => 'array', 'items' => ['type' => 'integer', 'format' => 'int32']])
        ->and($properties['nested'])->toBe(['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['anyOf' => [['type' => 'boolean'], ['type' => 'null']]]]]);
    expect(documentData($builder->build(new SchemaBuilder(), new Model(), [UnitJsonWithoutShape::class], false)))->toBe(['type' => 'object']);
    expect(fn() => $builder->build(new SchemaBuilder(), new Model(), ['missing-class'], false))->toThrow(ReflectionErrorException::class);
});
