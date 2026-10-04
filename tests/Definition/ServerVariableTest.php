<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\ServerVariable;
use function RaxosTests\OpenAPI\documentData;

covers(ServerVariable::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new ServerVariable(0, '', ['0', '1'])))->toBe(['default' => 0, 'description' => '', 'enum' => ['0', '1']]);
    expect(documentData(new ServerVariable('')))->toBe(['default' => '']);
});
