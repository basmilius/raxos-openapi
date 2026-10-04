<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\SecurityScheme;
use Raxos\OpenAPI\Enum\{SecuritySchemeType, SecurityType};
use function RaxosTests\OpenAPI\documentData;

covers(SecurityScheme::class);

it('serializes nested definitions and preserves meaningful empty values', function (): void {
    expect(documentData(new SecurityScheme(SecurityType::HTTP, SecuritySchemeType::BEARER, bearerFormat: 'JWT')))->toBe(['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'JWT']);
});
