<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\Example;
use function RaxosTests\OpenAPI\documentData;

covers(Example::class);

it('keeps false, zero, empty strings and arrays in examples', function (mixed $value): void {
    $data = documentData(new Example('value', $value));
    expect($data)->toHaveKey('value')->and($data['value'])->toBe($value);
})->with([false, 0, '', [[]]]);

it('omits absent optional example values', function (): void {
    expect(documentData(new Example('value', null)))->not->toHaveKey('value');
});
