<?php
declare(strict_types=1);

use Raxos\Collection\Paginated;
use Raxos\Http\HttpResponseCode;
use Raxos\OpenAPI\Attribute\{Property, Response};
use Raxos\OpenAPI\Definition\{MediaType, Reference, Response as ResponseDefinition, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\{UnitDto, UnitInvalidSchema, UnitShape, UnitStringable, UnitStringableModel};
use function RaxosTests\OpenAPI\documentData;

covers(SchemaBuilder::class);

it('preserves an explicit object schema on a Stringable model', function (): void {
    $builder = new SchemaBuilder();
    $schema = documentData($builder->auto(new Property(), [UnitStringableModel::class]));
    expect($schema)->toBe(['$ref' => '#/components/schemas/RaxosTests.OpenAPI.UnitStringableModel'])
        ->and(documentData($builder->schemas->get('RaxosTests.OpenAPI.UnitStringableModel')))->toMatchArray([
            'type' => 'object', 'properties' => ['name' => ['type' => 'string']],
        ]);
});

it('maps scalar aliases, literal booleans, unions and special object types', function (array $types, array $expected): void {
    expect(documentData(new SchemaBuilder()->auto(new Property(), $types)))->toBe($expected);
})->with([
    [['boolean'], ['type' => 'boolean']], [['true', 'null'], ['type' => ['boolean', 'null'], 'enum' => [true, null]]],
    [['false'], ['type' => 'boolean', 'enum' => [false]]], [['integer'], ['type' => 'integer', 'format' => 'int32']],
    [['double'], ['type' => 'number', 'format' => 'float']], [['iterable'], ['type' => 'array']],
    [['object'], ['type' => 'object']], [['null'], ['type' => 'null']], [[], ['type' => 'null']],
    [[DateTimeInterface::class], ['type' => 'string', 'format' => 'date-time']], [[UnitStringable::class], ['type' => 'string']],
    [[stdClass::class], ['type' => 'object']], [['int', 'string', 'null'], ['anyOf' => [['type' => 'integer', 'format' => 'int32'], ['type' => 'string'], ['type' => 'null']]]]
]);

it('removes incomplete schema reservations after errors and permits retries', function (): void {
    $builder = new SchemaBuilder();
    expect(fn() => $builder->reference(UnitInvalidSchema::class))->toThrow(Error::class);
    expect($builder->schemas->toArray())->toBe([]);
    expect(fn() => $builder->reference('MissingOpenAPIClass'))->toThrow(ReflectionErrorException::class);
    expect($builder->schemas->toArray())->toBe([])->and($builder->reference(UnitDto::class))->not->toBeNull();
    expect($builder->reference(stdClass::class))->toBeNull();
});

it('keeps distinct descriptions and content when a JSON response model is reused', function (): void {
    $builder = new SchemaBuilder();
    $first = $builder->response(new Response(HttpResponseCode::OK, 'Found', UnitShape::class));
    $second = $builder->response(new Response(HttpResponseCode::CREATED, 'Created', UnitShape::class, content: ['text/plain' => new MediaType(new Schema(type: SchemaType::STRING))]));
    $resolve = static function (mixed $value) use ($builder): array {
        if ($value instanceof Reference) {
            $value = $builder->responses->get(substr($value->to, strlen('#/components/responses/')));
        }

        return documentData($value);
    };
    expect($resolve($first)['description'])->toBe('Found')->and($resolve($second)['description'])->toBe('Created')
        ->and($resolve($second)['content'])->toHaveKeys(['text/plain', 'application/json']);
    expect($builder->response(new Response(HttpResponseCode::OK, 'Found', UnitShape::class)))->toEqual($first);
    expect(documentData($builder->response(new Response(HttpResponseCode::NO_CONTENT, 'Empty'))))->toBe(['description' => 'Empty']);
    expect($builder->buildBuiltIn(new Response(HttpResponseCode::OK, model: Paginated::class)))->toBeInstanceOf(ResponseDefinition::class);
});
