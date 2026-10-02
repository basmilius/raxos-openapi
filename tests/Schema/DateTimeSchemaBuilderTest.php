<?php
declare(strict_types=1);

use Raxos\OpenAPI\Attribute as A;
use Raxos\OpenAPI\Schema as B;
use Raxos\OpenAPI\SchemaBuilder;
use function RaxosTests\OpenAPI\documentData;

covers(B\DateTimeSchemaBuilder::class);

it('recognizes supported types and produces nullable schemas', function (bool $nullable): void {
    $builder = new B\DateTimeSchemaBuilder();
    expect($builder::can([DateTimeImmutable::class]))->toBeTrue()->and($builder::can(['array']))->toBeFalse()
        ->and(documentData($builder->build(new SchemaBuilder(), new A\Property(), [DateTimeImmutable::class], $nullable)))->toBe(['type' => $nullable ? ['string', 'null'] : 'string', 'format' => 'date-time']);
})->with([false, true]);
