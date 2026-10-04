<?php
declare(strict_types=1);

namespace RaxosTests\OpenAPI;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Database\Orm\Attribute\{Alias, Column, Hidden, PrimaryKey, Table};
use Raxos\Database\Orm\Model;
use Raxos\OpenAPI\Attribute\{Model as ModelAttribute, Property};
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
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

enum UnitEmptyInteger: int {}

enum UnitEmptyString: string {}

final class UnitStringable implements Stringable
{

    public function __toString(): string
    {
        return 'value';
    }

}

#[ModelAttribute]
final class UnitStringableModel implements Stringable
{

    #[Property]
    public string $name;

    public function __toString(): string
    {
        return $this->name;
    }

}

#[ModelAttribute]
class UnitDto
{

    #[Property(alias: 'renamed')]
    public string $name;
    #[Property]
    #[Hidden]
    public string $secret;
    #[Property]
    #[Alias('alias')]
    public int $identifier;
    #[Property]
    #[Alias('other')]
    #[Column('physical')]
    public bool $flag;
    #[Property(schema: new Schema(type: SchemaType::STRING, pattern: '^A'))]
    public string $code;
    public string $undocumented;

}

#[ModelAttribute]
#[Table('unit_openapi')]
final class UnitOrm extends Model
{

    #[PrimaryKey]
    #[Property]
    public int $id;

}

#[ModelAttribute]
final class UnitRequest implements HttpRequestModelInterface
{

    #[Property]
    public string $name;

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

#[ModelAttribute]
final class UnitInvalidSchema
{

    #[Property(unknown: true)]
    public string $value;

}
