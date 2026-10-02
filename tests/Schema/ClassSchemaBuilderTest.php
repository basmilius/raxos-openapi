<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\ClassSchemaBuilder::class);

it('honors explicit schemas, aliases and hidden ORM fields', function (): void {
    $builder = new B\ClassSchemaBuilder();
    expect($builder::can([F\UnitDto::class]))->toBeTrue()->and($builder::can(['does-not-exist']))->toBeFalse();
    $data = documentData($builder->build(new SchemaBuilder(), new A\Model(), [F\UnitDto::class], true));
    expect($data['type'])->toBe(['object', 'null'])->and(array_keys($data['properties']))->toBe(['renamed', 'alias', 'physical', 'code'])
        ->and($data['properties']['code'])->toBe(['type' => 'string', 'pattern' => '^A']);
});

it('wraps reflection failures with their original cause', function (): void {
    try {
        new B\ClassSchemaBuilder()->build(new SchemaBuilder(), new A\Model(), ['missing-class'], false);
        $this->fail('Expected reflection failure.');
    } catch (Raxos\OpenAPI\Error\ReflectionErrorException $exception) {
        expect($exception->getPrevious())->toBeInstanceOf(ReflectionException::class);
    }
});
