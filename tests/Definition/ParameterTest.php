<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Parameter, Schema};
use Raxos\OpenAPI\Enum\{In, SchemaType};
use function RaxosTests\OpenAPI\documentData;

covers(Parameter::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Parameter('page', In::QUERY, schema: new Schema(type: SchemaType::INTEGER, minimum: 0))))->toBe(['name' => 'page', 'in' => 'query', 'required' => false, 'deprecated' => false, 'allowEmptyValue' => false, 'schema' => ['type' => 'integer', 'minimum' => 0]]);
});
