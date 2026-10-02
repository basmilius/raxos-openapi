<?php
declare(strict_types=1);

use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use RaxosTests\OpenAPI as F;
use function RaxosTests\OpenAPI\documentData;

covers(B\BuiltinSchemaBuilder::class);

it('describes item types consistently for collection and pagination responses', function (string $class, ?string $generic, ?array $items): void {
    $data = documentData(B\BuiltinSchemaBuilder::build(new SchemaBuilder(), $class, $generic));
    $schema = $data['content']['application/json']['schema'];
    if ($class === Raxos\Collection\Paginated::class) {
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
    [Raxos\Contract\Collection\ArrayListInterface::class, null, null],
    [Raxos\Contract\Collection\ArrayListInterface::class, 'int', ['type' => 'integer', 'format' => 'int32']],
    [Raxos\Collection\Paginated::class, null, null],
    [Raxos\Collection\Paginated::class, 'string', ['type' => 'string']],
    [Raxos\Collection\Paginated::class, F\UnitDto::class, ['$ref' => '#/components/schemas/'.str_replace('\\', '.', F\UnitDto::class)]]
]);

it('does not interpret unknown response classes as built-in collections', function (): void {
    expect(B\BuiltinSchemaBuilder::build(new SchemaBuilder(), stdClass::class, null))->toBeNull();
});
