<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use Raxos\OpenAPI\Enum as E;
use function RaxosTests\OpenAPI\documentData;

covers(D\Parameter::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Parameter('page', E\In::QUERY, schema: new D\Schema(type: E\SchemaType::INTEGER, minimum: 0))))->toBe(['name' => 'page', 'in' => 'query', 'required' => false, 'deprecated' => false, 'allowEmptyValue' => false, 'schema' => ['type' => 'integer', 'minimum' => 0]]);
});
