<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{MediaType, Schema};
use Raxos\OpenAPI\Enum\SchemaType;
use function RaxosTests\OpenAPI\documentData;

covers(MediaType::class);

it('keeps false, zero, empty strings and arrays in examples', function (mixed $value): void {
    $data = documentData(new MediaType(new Schema(type: SchemaType::BOOLEAN), $value));
    expect($data)->toHaveKey('example')->and($data['example'])->toBe($value);
})->with([false, 0, '', [[]]]);

it('omits absent optional example values', function (): void {
    expect(documentData(new MediaType(new Schema(type: SchemaType::BOOLEAN), null)))->not->toHaveKey('example');
});
