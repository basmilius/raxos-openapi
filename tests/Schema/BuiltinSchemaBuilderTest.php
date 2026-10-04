<?php
declare(strict_types=1);

use Opis\JsonSchema\Validator;
use Raxos\Collection\CursorPage;
use Raxos\Collection\Paginated;
use Raxos\Contract\Collection\ArrayListInterface;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\BuiltinSchemaBuilder::class);

it('describes item types consistently for collection and pagination responses', function (string $class, ?string $generic, ?array $items): void {
    $data = documentData(B\BuiltinSchemaBuilder::build(new SchemaBuilder(), $class, $generic));
    $schema = $data['content']['application/json']['schema'];

    if ($class === Paginated::class) {
        expect(array_keys($schema['properties']))->toBe(['items', 'page', 'page_size', 'pages', 'total']);
        $schema = $schema['properties']['items'];
    }
    expect($schema['type'])->toBe('array');

    if ($items === null) {
        expect($schema)->not->toHaveKey('items');
    } else {
        expect($schema['items'])->toBe($items);
    }
})->with([
    [ArrayListInterface::class, null, null],
    [ArrayListInterface::class, 'int', ['type' => 'integer', 'format' => 'int32']],
    [Paginated::class, null, null],
    [Paginated::class, 'string', ['type' => 'string']],
    [Paginated::class, F\UnitDto::class, ['$ref' => '#/components/schemas/' . str_replace('\\', '.', F\UnitDto::class)]]
]);

it('does not interpret unknown response classes as built-in collections', function (): void {
    expect(B\BuiltinSchemaBuilder::build(new SchemaBuilder(), stdClass::class, null))->toBeNull();
});


it('describes cursor pages without a total and validates their nullable continuation', function (): void {
    $data = documentData(B\BuiltinSchemaBuilder::build(new SchemaBuilder(), CursorPage::class, 'int'));
    $schema = $data['content']['application/json']['schema'];
    expect(array_keys($schema['properties']))->toBe(['items', 'next_cursor', 'has_more']);
    $compiled = json_decode(json_encode($schema));
    expect(new Validator()->validate((object)['items' => [1, 2], 'next_cursor' => null, 'has_more' => false], $compiled)->isValid())->toBeTrue()
        ->and(new Validator()->validate((object)['items' => ['wrong'], 'next_cursor' => null, 'has_more' => false], $compiled)->isValid())->toBeFalse();
});
