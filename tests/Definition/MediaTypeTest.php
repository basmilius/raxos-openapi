<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use Raxos\OpenAPI\Enum as E;
use function RaxosTests\OpenAPI\documentData;

covers(D\MediaType::class);

it('keeps false, zero, empty strings and arrays in examples', function (mixed $value): void {
    $data = documentData(new D\MediaType(new D\Schema(type: E\SchemaType::BOOLEAN), $value));
    expect($data)->toHaveKey('example')->and($data['example'])->toBe($value);
})->with([false, 0, '', [[]]]);

it('omits absent optional example values', function (): void {
    expect(documentData(new D\MediaType(new D\Schema(type: E\SchemaType::BOOLEAN), null)))->not->toHaveKey('example');
});
