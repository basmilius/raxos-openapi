<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Contact, Info, License};
use function RaxosTests\OpenAPI\documentData;

covers(Info::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new Info('API', '3.2.0', summary: '', contact: new Contact('Bas'), license: new License('MIT'))))->toBe(['title' => 'API', 'summary' => '', 'contact' => ['name' => 'Bas'], 'license' => ['name' => 'MIT'], 'version' => '3.2.0']);
    expect(documentData(new Info('API', '3.2.0')))->toBe(['title' => 'API', 'version' => '3.2.0']);
});
