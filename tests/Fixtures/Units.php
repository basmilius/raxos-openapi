<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Database\Orm\Attribute as ORM;
use Raxos\Database\Orm\Model;
use Raxos\OpenAPI\Attribute as API;
use Stringable;
use Throwable;

function documentData(mixed $value): array
{
    return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
}

enum UnitInteger: int
{
    case ZERO = 0;
    case ONE = 1;
}
enum UnitEmptyInteger: int
{
}
enum UnitEmptyString: string
{
}

final class UnitStringable implements Stringable
{
    public function __toString(): string
    {
        return 'value';
    }
}

#[API\Model]
final class UnitStringableModel implements Stringable
{
    #[API\Property]
    public string $name;

    public function __toString(): string
    {
        return $this->name;
    }
}

#[API\Model]
class UnitDto
{
    #[API\Property(alias: 'renamed')] public string $name;
    #[API\Property] #[ORM\Hidden] public string $secret;
    #[API\Property] #[ORM\Alias('alias')] public int $identifier;
    #[API\Property] #[ORM\Alias('other')] #[ORM\Column('physical')] public bool $flag;
    #[API\Property(schema: new \Raxos\OpenAPI\Definition\Schema(type: \Raxos\OpenAPI\Enum\SchemaType::STRING, pattern: '^A'))] public string $code;
    public string $undocumented;
}

#[API\Model]
#[ORM\Table('unit_openapi')]
final class UnitOrm extends Model
{
    #[ORM\PrimaryKey] #[API\Property] public int $id;
}

#[API\Model]
final class UnitRequest implements HttpRequestModelInterface
{
    #[API\Property] public string $name;
}

final class UnitShape implements JsonSerializable
{
    #[ArrayShape(['optional' => '?string', 'list' => 'list<int>', 'error' => Throwable::class, 'nested' => 'array<string,list<bool|null>>'])]
    public function jsonSerialize(): array
    {
        return [];
    }
}

final class UnitJsonWithoutShape implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [];
    }
}

#[API\Model]
final class UnitInvalidSchema
{
    #[API\Property(unknown: true)] public string $value;
}
