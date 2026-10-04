<?php
declare(strict_types=1);

use Opis\JsonSchema\Validator;
use Raxos\Error\InvalidArgumentException;
use Raxos\Http\Validate\Constraint\Matches;
use Raxos\Http\Validate\Error\ValidationNotOkException;
use Raxos\Http\Validate\HttpClassValidator;
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\Schema\RequestSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI\ConditionalContract;
use RaxosTests\OpenAPI\ContractRequest;
use function RaxosTests\OpenAPI\documentData;

covers(RequestSchemaBuilder::class);

it('validates canonical inputs identically in the runtime and the generated schema', function (string $field, mixed $value, bool $valid): void {
    $data = ['display_name' => 'Ada', 'count' => 2, 'state' => 1, 'address' => ['city' => 'Utrecht'], 'addresses' => [['city' => 'Paris']], 'requiredNullable' => 'present'];

    if ($field === 'missing') {
        unset($data[$value]);
    } else {
        $data[$field] = $value;
    }
    $runtime = new HttpClassValidator(ContractRequest::class);
    $runtime->validate($data);
    $accepted = true;

    try {
        $runtime->get();
    } catch (ValidationNotOkException) {
        $accepted = false;
    }
    $builder = new SchemaBuilder();
    $ref = $builder->requestReference(ContractRequest::class);
    $schema = json_decode(json_encode(['$ref' => $ref->jsonSerialize()['$ref'], 'components' => ['schemas' => $builder->schemas]], JSON_THROW_ON_ERROR));
    $payload = json_decode(json_encode($data, JSON_THROW_ON_ERROR));
    expect($accepted)->toBe($valid)->and(new Validator()->validate($payload, $schema)->isValid())->toBe($valid);
})->with([
    ['display_name', 'Grace', true], ['display_name', 'x', false], ['display_name', 'very long full name', false],
    ['count', 0, false], ['count', 11, false], ['count', 10, true],
    ['state', 9, false], ['state', 0, true], ['state', null, false],
    ['address', ['city' => 'x'], false], ['addresses', [['city' => 'x']], false], ['addresses', [], true],
    ['requiredNullable', null, false], ['missing', 'requiredNullable', false], ['note', null, true],
]);

it('keeps input aliases, required fields and defaults distinct from response components', function (): void {
    $builder = new SchemaBuilder();
    $request = $builder->requestReference(ContractRequest::class);
    $response = $builder->reference(ContractRequest::class);
    $all = documentData($builder->schemas);
    $input = $all[substr($request->jsonSerialize()['$ref'], strlen('#/components/schemas/'))];
    $output = $all[substr($response->jsonSerialize()['$ref'], strlen('#/components/schemas/'))];
    expect($input['required'])->toContain('display_name', 'count', 'requiredNullable')->not->toContain('note', 'limit')
        ->and($input['properties']['limit']['default'])->toBe(5)
        ->and($input['properties'])->toHaveKey('display_name')->not->toHaveKey('output_name')
        ->and($output['properties'])->toHaveKey('output_name')->not->toHaveKey('display_name');
    $runtime = new HttpClassValidator(ContractRequest::class);
    $runtime->validate(['display_name' => 'Ada', 'count' => 1, 'state' => 0, 'address' => ['city' => 'Paris'], 'addresses' => [], 'requiredNullable' => 'present']);
    expect($runtime->get()->limit)->toBe(5)->and($runtime->get()->note)->toBeNull();
});

it('marks conditional and unmapped rules without executing them and honors overrides', function (): void {
    $builder = new SchemaBuilder();
    $ref = $builder->requestReference(ConditionalContract::class);
    $schema = documentData($builder->schemas)[substr($ref->jsonSerialize()['$ref'], strlen('#/components/schemas/'))];
    expect($schema['x-raxos-conditional-required'])->toBe(['conditional'])
        ->and($schema['required'])->toContain('manual', 'pattern', 'explicit')
        ->and($schema['properties']['pattern']['x-raxos-runtime-constraints'])->toBe([Matches::class])
        ->and($schema['properties']['explicit']['pattern'])->toBe('^B')
        ->and($builder->diagnostics->count())->toBe(2);
});

it('prevents extensions from overriding standard schema members', function (): void {
    expect(fn() => new Schema(type: SchemaType::STRING, extensions: ['type' => 'integer']))->toThrow(InvalidArgumentException::class)
        ->and(documentData(new Schema(extensions: ['x-example' => false])))->toBe(['x-example' => false]);
});
