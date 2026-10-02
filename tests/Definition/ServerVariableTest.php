<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\ServerVariable::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\ServerVariable(0, '', ['0', '1'])))->toBe(['default' => 0, 'description' => '', 'enum' => ['0', '1']]);
    expect(documentData(new D\ServerVariable('')))->toBe(['default' => '']);
});
