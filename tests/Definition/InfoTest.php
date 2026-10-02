<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use function RaxosTests\OpenAPI\documentData;

covers(D\Info::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\Info('API', '3.2.0', summary: '', contact: new D\Contact('Bas'), license: new D\License('MIT'))))->toBe(['title' => 'API', 'summary' => '', 'contact' => ['name' => 'Bas'], 'license' => ['name' => 'MIT'], 'version' => '3.2.0']);
    expect(documentData(new D\Info('API', '3.2.0')))->toBe(['title' => 'API', 'version' => '3.2.0']);
});
