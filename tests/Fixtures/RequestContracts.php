<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Http\Validate\Attribute\Property;
use Raxos\Http\Validate\Constraint as C;
use Raxos\OpenAPI\Attribute as API;
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;

final readonly class ContractAddress implements HttpRequestModelInterface
{
    public function __construct(#[Property] #[C\MinLength(2)] public string $city) {}
}

#[API\Model]
final readonly class ContractRequest implements HttpRequestModelInterface
{
    public function __construct(
        #[Property(alias: 'display_name')] #[C\MinLength(2)] #[C\MaxLength(12)] #[API\Property(alias: 'output_name')] public string $name,
        #[Property] #[C\Min(1)] #[C\Max(10)] public int $count,
        #[Property] public UnitInteger $state,
        #[Property] public ContractAddress $address,
        #[Property] #[C\NestedArray(ContractAddress::class)] public array $addresses,
        #[Property] public ?string $requiredNullable,
        #[Property(optional: true)] public ?string $note = null,
        #[Property(optional: true)] public int $limit = 5,
    ) {}
}

final readonly class ConditionalContract implements HttpRequestModelInterface
{
    public function __construct(
        #[Property(optional: self::optional(...))] public ?string $conditional = null,
        #[Property(optional: self::optional(...))] #[API\Required] public string $manual = 'default',
        #[Property] #[C\Matches('/^A/')] public string $pattern = 'A',
        #[Property] #[API\Property(schema: new Schema(type: SchemaType::STRING, pattern: '^B'))] public string $explicit = 'B',
    ) {}

    public static function optional(): bool
    {
        throw new \RuntimeException('Schema generation must not invoke request-dependent rules.');
    }
}
