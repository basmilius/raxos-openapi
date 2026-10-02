<?php
declare(strict_types=1);


covers(Raxos\OpenAPI\DefinitionHelper::class);

it('distinguishes absent values from false and zero', function (): void {
    expect(Raxos\OpenAPI\DefinitionHelper::isNotNull(false))->toBeTrue()->and(Raxos\OpenAPI\DefinitionHelper::isNotNull(0))->toBeTrue()
        ->and(Raxos\OpenAPI\DefinitionHelper::isNotNull(null))->toBeFalse()->and(Raxos\OpenAPI\DefinitionHelper::isNotEmpty([]))->toBeFalse()
        ->and(Raxos\OpenAPI\DefinitionHelper::isNotEmpty(['item']))->toBeTrue();
});

it('normalizes alternatives with named OAuth scopes and an anonymous security option', function (): void {
    expect(iterator_to_array(Raxos\OpenAPI\DefinitionHelper::normalizeSecurity(['bearer', 'oauth' => ['read', 'write']])))->toBe([['bearer' => []], ['oauth' => ['read', 'write']]]);
});
