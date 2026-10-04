<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute\Property;
use Raxos\OpenAPI\Schema\DateTimeSchemaBuilder;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(DateTimeSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new DateTimeSchemaBuilder();
    expect($builder::can([DateTimeImmutable::class]))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new Property(), [DateTimeImmutable::class], $nullable)))->toBe(['type' => $nullable ? ['string', 'null'] : 'string', 'format' => 'date-time']);
})->with([false, true]);
