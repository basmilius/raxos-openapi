<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\Contact;
use function RaxosTests\OpenAPI\documentData;

covers(Contact::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Contact('Bas', 'bas@example.test', 'https://example.test')))->toBe(['name' => 'Bas', 'email' => 'bas@example.test', 'url' => 'https://example.test']);
    expect(documentData(new Contact('Bas')))->toBe(['name' => 'Bas']);
});
