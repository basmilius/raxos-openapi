<?php
declare(strict_types=1);

use Opis\JsonSchema\Validator;
use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Definition\{Reference, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\SchemaBuilder;
use Raxos\OpenAPI\Tests\Fixtures\{JsonTree, State, Tree};

it('describes recursive DTOs with booleans, arrays and nullable references', function (): void {
    $builder = new SchemaBuilder();
    $builder->reference(Tree::class);
    $schemas = json_decode(json_encode($builder->schemas, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    $tree = $schemas[str_replace('\\', '.', Tree::class)]['properties'];
    expect($tree['active'])->toBe(['type' => 'boolean'])
        ->and($tree['children'])->toBe(['type' => 'array'])
        ->and($tree['parent']['anyOf'][0]['$ref'])->toBe('#/components/schemas/' . str_replace('\\', '.', Tree::class))
        ->and($tree['parent']['anyOf'][1])->toBe(['type' => 'null'])
        ->and($tree['optionalState']['anyOf'][1])->toBe(['type' => 'null'])
        ->and($tree['state'])->toHaveKey('$ref')
        ->and($schemas[str_replace('\\', '.', State::class)])->toBe(['type' => 'string', 'enum' => ['ready']])
        ->and($tree['identifier']['anyOf'])->toHaveCount(3)
        ->and($schemas)->toHaveCount(2);
});

it('reserves recursive ArrayShape components and keeps nested generic types', function (): void {
    $builder = new SchemaBuilder();
    $reference = $builder->reference(JsonTree::class);
    $schemas = json_decode(json_encode($builder->schemas, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    $properties = $schemas[str_replace('\\', '.', JsonTree::class)]['properties'];
    expect($schemas)->toHaveCount(1)
        ->and($properties['parent']['anyOf'][0])->toBe($reference->jsonSerialize())
        ->and($properties['children'])->toBe(['type' => 'array', 'items' => $reference->jsonSerialize()])
        ->and($properties['dictionary'])->toBe(['type' => 'object', 'additionalProperties' => $reference->jsonSerialize()])
        ->and($properties['nested']['items']['additionalProperties']['anyOf'])->toHaveCount(3)
        ->and($properties['active'])->toBe(['type' => 'boolean'])
        ->and($properties['untyped']['type'])->toContain('null', 'object', 'array');
});

it('emits OpenAPI 3.1 numeric constraints and null unions', function (): void {
    $schema = new Schema(type: SchemaType::NUMBER, nullable: true, minimum: 0.5, maximum: 2.5, exclusiveMinimum: true, multipleOf: 0.25);
    $data = json_decode(json_encode($schema, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    expect($data['type'])->toBe(['number', 'null'])
        ->and($data)->not->toHaveKey('nullable')
        ->and($data['exclusiveMinimum'])->toBe(0.5)
        ->and($data['multipleOf'])->toBe(0.25)
        ->and(json_encode(new Schema(), JSON_THROW_ON_ERROR))->toBe('{}')
        ->and(json_encode(new Schema(type: SchemaType::OBJECT, properties: [], additionalProperties: false), JSON_THROW_ON_ERROR))->toBe('{"type":"object","properties":{},"additionalProperties":false}');
});

it('allows null in nullable literal enums and supports null-first unions', function (): void {
    $builder = new SchemaBuilder();
    $schema = $builder->auto(new Property(), ['null', 'false']);
    $data = json_decode(json_encode($schema, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    expect($data)->toBe(['type' => ['boolean', 'null'], 'enum' => [false, null]]);
});

it('validates recursive DTO payloads against their generated JSON Schema', function (mixed $active, bool $valid): void {
    $builder = new SchemaBuilder();
    $reference = $builder->reference(Tree::class);
    $schema = json_decode(json_encode(['$ref' => $reference->jsonSerialize()['$ref'], 'components' => ['schemas' => $builder->schemas]], JSON_THROW_ON_ERROR));
    $payload = (object)['active' => $active, 'children' => [], 'parent' => null, 'state' => 'ready', 'optionalState' => null, 'identifier' => 0];
    expect(new Validator()->validate($payload, $schema)->isValid())->toBe($valid);
})->with([[true, true], [false, true], ['true', false], [1, false], [null, false]]);

it('validates nullable number constraints including exclusive bounds and multiples', function (int|float|null $value, bool $valid): void {
    $schema = json_decode(json_encode(new Schema(type: SchemaType::NUMBER, nullable: true, minimum: 0.5, exclusiveMinimum: true, maximum: 2.5, multipleOf: 0.25), JSON_THROW_ON_ERROR));
    expect(new Validator()->validate($value, $schema)->isValid())->toBe($valid);
})->with([[null, true], [0.5, false], [0.75, true], [2.5, true], [2.75, false], [0.8, false]]);

it('does not weaken canonical components when a nullable reference is requested first', function (): void {
    $builder = new SchemaBuilder();
    $nullable = $builder->reference(Tree::class, true);
    $required = $builder->reference(Tree::class);
    $schemas = json_decode(json_encode($builder->schemas), true);
    expect($nullable)->toBeInstanceOf(Schema::class)
        ->and($required)->toBeInstanceOf(Reference::class)
        ->and($schemas[str_replace('\\', '.', Tree::class)]['type'])->toBe('object');
});
