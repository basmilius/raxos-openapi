<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\RequestModelSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\{UnitDto, UnitRequest};
use function RaxosTests\OpenAPI\documentData;

covers(RequestModelSchemaBuilder::class);

it('references supported models without changing canonical components for nullable uses', function (): void {
    $builder = new RequestModelSchemaBuilder();
    $schemas = new SchemaBuilder();
    expect($builder::can([UnitRequest::class]))->toBeTrue()->and($builder::can([UnitDto::class]))->toBeFalse();
    $data = documentData($builder->build($schemas, new Property(), [UnitRequest::class], true));
    expect($data['anyOf'][0]['$ref'])->toBe('#/components/schemas/' . str_replace('\\', '.', UnitRequest::class))
        ->and($data['anyOf'][1])->toBe(['type' => 'null']);
});
