<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Model;
use Raxos\OpenAPI\Error\ReflectionErrorException;
use Raxos\OpenAPI\Schema\ClassSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\UnitDto;
use function RaxosTests\OpenAPI\documentData;

covers(ClassSchemaBuilder::class);

it('honors explicit schemas, aliases and hidden ORM fields', function (): void {
    $builder = new ClassSchemaBuilder();
    expect($builder::can([UnitDto::class]))->toBeTrue()->and($builder::can(['does-not-exist']))->toBeFalse();
    $data = documentData($builder->build(new SchemaBuilder(), new Model(), [UnitDto::class], true));
    expect($data['type'])->toBe(['object', 'null'])->and(array_keys($data['properties']))->toBe(['renamed', 'alias', 'physical', 'code'])
        ->and($data['properties']['code'])->toBe(['type' => 'string', 'pattern' => '^A']);
});

it('wraps reflection failures with their original cause', function (): void {
    try {
        new ClassSchemaBuilder()->build(new SchemaBuilder(), new Model(), ['missing-class'], false);
        $this->fail('Expected reflection failure.');
    } catch (ReflectionErrorException $exception) {
        expect($exception->getPrevious())->toBeInstanceOf(ReflectionException::class);
    }
});
