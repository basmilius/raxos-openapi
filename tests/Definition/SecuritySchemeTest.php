<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition as D;
use Raxos\OpenAPI\Enum as E;
use function RaxosTests\OpenAPI\documentData;

covers(D\SecurityScheme::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new D\SecurityScheme(E\SecurityType::HTTP, E\SecuritySchemeType::BEARER, bearerFormat: 'JWT')))->toBe(['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'JWT']);
});
