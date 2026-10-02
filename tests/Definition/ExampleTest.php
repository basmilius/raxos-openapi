<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Example::class);

it('keeps false, zero, empty strings and arrays in examples', function (mixed $value): void {
    $data = documentData(new D\Example('value', $value));
    expect($data)->toHaveKey('value')->and($data['value'])->toBe($value);
})->with([false, 0, '', [[]]]);

it('omits absent optional example values', function (): void {
    expect(documentData(new D\Example('value', null)))->not->toHaveKey('value');
});
