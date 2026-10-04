<?php
declare(strict_types=1);

use Raxos\OpenAPI\DefinitionHelper;

covers(DefinitionHelper::class);

it('distinguishes absent values from false and zero', function (): void {
    expect(DefinitionHelper::isNotNull(false))->toBeTrue()->and(DefinitionHelper::isNotNull(0))->toBeTrue()
        ->and(DefinitionHelper::isNotNull(null))->toBeFalse()->and(DefinitionHelper::isNotEmpty([]))->toBeFalse()
        ->and(DefinitionHelper::isNotEmpty(['item']))->toBeTrue();
});

it('normalizes alternatives with named OAuth scopes and an anonymous security option', function (): void {
    expect(iterator_to_array(DefinitionHelper::normalizeSecurity(['bearer', 'oauth' => ['read', 'write']])))->toBe([['bearer' => []], ['oauth' => ['read', 'write']]]);
});
