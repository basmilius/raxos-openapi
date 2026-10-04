<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\RequestModelSchemaBuilder::class);

it('references supported models without changing canonical components for nullable uses', function (): void {
    $builder = new B\RequestModelSchemaBuilder();
    $schemas = new SchemaBuilder();
    expect($builder::can([F\UnitRequest::class]))->toBeTrue()->and($builder::can([F\UnitDto::class]))->toBeFalse();
    $data = documentData($builder->build($schemas, new A\Property(), [F\UnitRequest::class], true));
    expect($data['anyOf'][0]['$ref'])->toBe('#/components/schemas/' . str_replace('\\', '.', F\UnitRequest::class))
        ->and($data['anyOf'][1])->toBe(['type' => 'null']);
});
