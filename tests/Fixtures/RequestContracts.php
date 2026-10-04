<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Http\Validate\Attribute\Property;
use Raxos\Http\Validate\Constraint\{Matches, Max, MaxLength, Min, MinLength, NestedArray};
use Raxos\OpenAPI\Attribute\{Model, Property as PropertyAttribute, Required};
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
use RuntimeException;

final readonly class ContractAddress implements HttpRequestModelInterface
{

    public function __construct(#[Property] #[MinLength(2)] public string $city) {}

}

#[Model]
final readonly class ContractRequest implements HttpRequestModelInterface
{

    public function __construct(
        #[Property(alias: 'display_name')] #[MinLength(2)] #[MaxLength(12)] #[PropertyAttribute(alias: 'output_name')] public string $name,
        #[Property] #[Min(1)] #[Max(10)] public int $count,
        #[Property] public UnitInteger $state,
        #[Property] public ContractAddress $address,
        #[Property] #[NestedArray(ContractAddress::class)] public array $addresses,
        #[Property] public ?string $requiredNullable,
        #[Property(optional: true)] public ?string $note = null,
        #[Property(optional: true)] public int $limit = 5,
    ) {}

}

final readonly class ConditionalContract implements HttpRequestModelInterface
{

    public function __construct(
        #[Property(optional: self::optional(...))] public ?string $conditional = null,
        #[Property(optional: self::optional(...))] #[Required] public string $manual = 'default',
        #[Property] #[Matches('/^A/')] public string $pattern = 'A',
        #[Property] #[PropertyAttribute(schema: new Schema(type: SchemaType::STRING, pattern: '^B'))] public string $explicit = 'B',
    ) {}

    public static function optional(): bool
    {
        throw new RuntimeException('Schema generation must not invoke request-dependent rules.');
    }

}
