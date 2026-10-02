<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Tests\Fixtures;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Raxos\OpenAPI\Attribute as API;

#[API\Model]
final readonly class Tree
{
    public function __construct(
        #[API\Property] public bool $active,
        #[API\Property] public array $children,
        #[API\Property] public ?Tree $parent,
        #[API\Property] public State|null $optionalState,
        #[API\Property] public State $state,
        #[API\Property] public int|string|null $identifier
    ) {}
}

enum State: string
{
    case READY = 'ready';
}

final readonly class JsonTree implements JsonSerializable
{
    #[ArrayShape([
        'parent' => self::class . '|null',
        'children' => self::class . '[]',
        'dictionary' => 'array<string, ' . self::class . '>',
        'nested' => 'array<int, array<string, bool|int|null>>',
        'active' => 'bool',
        'untyped' => 'mixed'
    ])]
    public function jsonSerialize(): array
    {
        return ['parent' => null, 'children' => [], 'dictionary' => [], 'nested' => [], 'active' => true, 'untyped' => null];
    }
}
