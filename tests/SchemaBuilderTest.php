<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(SchemaBuilder::class);

it('preserves an explicit object schema on a Stringable model', function (): void {
    $builder = new SchemaBuilder();
    $schema = documentData($builder->auto(new A\Property(), [F\UnitStringableModel::class]));
    expect($schema)->toBe(['$ref' => '#/components/schemas/RaxosTests.OpenAPI.UnitStringableModel'])
        ->and(documentData($builder->schemas->get('RaxosTests.OpenAPI.UnitStringableModel')))->toMatchArray([
            'type' => 'object', 'properties' => ['name' => ['type' => 'string']],
        ]);
});

it('maps scalar aliases, literal booleans, unions and special object types', function (array $types, array $expected): void {
    expect(documentData(new SchemaBuilder()->auto(new A\Property(), $types)))->toBe($expected);
})->with([
    [['boolean'], ['type' => 'boolean']], [['true', 'null'], ['type' => ['boolean', 'null'], 'enum' => [true, null]]],
    [['false'], ['type' => 'boolean', 'enum' => [false]]], [['integer'], ['type' => 'integer', 'format' => 'int32']],
    [['double'], ['type' => 'number', 'format' => 'float']], [['iterable'], ['type' => 'array']],
    [['object'], ['type' => 'object']], [['null'], ['type' => 'null']], [[], ['type' => 'null']],
    [[DateTimeInterface::class], ['type' => 'string', 'format' => 'date-time']], [[F\UnitStringable::class], ['type' => 'string']],
    [[stdClass::class], ['type' => 'object']], [['int', 'string', 'null'], ['anyOf' => [['type' => 'integer', 'format' => 'int32'], ['type' => 'string'], ['type' => 'null']]]]
]);

it('removes incomplete schema reservations after errors and permits retries', function (): void {
    $builder = new SchemaBuilder();
    expect(fn () => $builder->reference(F\UnitInvalidSchema::class))->toThrow(Error::class);
    expect($builder->schemas->toArray())->toBe([]);
    expect(fn () => $builder->reference('MissingOpenAPIClass'))->toThrow(Raxos\OpenAPI\Error\ReflectionErrorException::class);
    expect($builder->schemas->toArray())->toBe([])->and($builder->reference(F\UnitDto::class))->not->toBeNull();
    expect($builder->reference(stdClass::class))->toBeNull();
});

it('keeps distinct descriptions and content when a JSON response model is reused', function (): void {
    $builder = new SchemaBuilder();
    $first = $builder->response(new A\Response(Raxos\Http\HttpResponseCode::OK, 'Found', F\UnitShape::class));
    $second = $builder->response(new A\Response(Raxos\Http\HttpResponseCode::CREATED, 'Created', F\UnitShape::class, content: ['text/plain' => new Raxos\OpenAPI\Definition\MediaType(new Raxos\OpenAPI\Definition\Schema(type: Raxos\OpenAPI\Enum\SchemaType::STRING))]));
    $resolve = static function (mixed $value) use ($builder): array {
        if ($value instanceof Raxos\OpenAPI\Definition\Reference) {
            $value = $builder->responses->get(substr($value->to, strlen('#/components/responses/')));
        }
        return documentData($value);
    };
    expect($resolve($first)['description'])->toBe('Found')->and($resolve($second)['description'])->toBe('Created')
        ->and($resolve($second)['content'])->toHaveKeys(['text/plain', 'application/json']);
    expect($builder->response(new A\Response(Raxos\Http\HttpResponseCode::OK, 'Found', F\UnitShape::class)))->toEqual($first);
    expect(documentData($builder->response(new A\Response(Raxos\Http\HttpResponseCode::NO_CONTENT, 'Empty'))))->toBe(['description' => 'Empty']);
    expect($builder->buildBuiltIn(new A\Response(Raxos\Http\HttpResponseCode::OK, model: Raxos\Collection\Paginated::class)))->toBeInstanceOf(Raxos\OpenAPI\Definition\Response::class);
});
