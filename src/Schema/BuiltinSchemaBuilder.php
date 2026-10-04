<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Schema;

use Raxos\Collection\CursorPage;
use Raxos\Collection\Paginated;
use Raxos\Contract\Collection\ArrayListInterface;
use Raxos\Contract\OpenAPI\OpenAPIExceptionInterface;
use Raxos\OpenAPI\Attribute as Attr;
use Raxos\OpenAPI\Definition\MediaType;
use Raxos\OpenAPI\Definition\Reference;
use Raxos\OpenAPI\Definition\Response;
use Raxos\OpenAPI\Definition\Schema;
use Raxos\OpenAPI\Enum\SchemaType;
use Raxos\OpenAPI\SchemaBuilder;

/**
 * Class BuiltinSchemaBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Schema
 * @since 2.1.0
 */
final class BuiltinSchemaBuilder
{

    public const array BUILTINS = [
        ArrayListInterface::class,
        Paginated::class,
        CursorPage::class
    ];

    /**
     * Builds a builtin type.
     *
     * @param SchemaBuilder $builder
     * @param string $class
     * @param string|null $genericClass
     *
     * @return Reference|Response|Schema|null
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    public static function build(
        SchemaBuilder $builder,
        string $class,
        ?string $genericClass
    ): Reference|Response|Schema|null
    {
        return match ($class) {
            ArrayListInterface::class => self::buildArrayList($builder, $genericClass),
            Paginated::class => self::buildPaginated($builder, $genericClass),
            CursorPage::class => self::buildCursorPage($builder, $genericClass),
            default => null
        };
    }

    /**
     * Builds an array list response.
     *
     * @param SchemaBuilder $builder
     * @param string|null $genericClass
     *
     * @return Response
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    public static function buildArrayList(
        SchemaBuilder $builder,
        ?string $genericClass
    ): Response
    {
        return self::response(
            new Schema(
                type: SchemaType::ARRAY,
                items: $genericClass !== null
                    ? $builder->auto(new Attr\Model(), [$genericClass])
                    : null
            )
        );
    }

    /**
     * Builds a paginated response.
     *
     * @param SchemaBuilder $builder
     * @param string|null $genericClass
     *
     * @return Response
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    public static function buildPaginated(
        SchemaBuilder $builder,
        ?string $genericClass
    ): Response
    {
        return self::response(
            new Schema(
                type: SchemaType::OBJECT,
                properties: [
                    'items' => new Schema(
                        type: SchemaType::ARRAY,
                        items: $genericClass !== null
                            ? $builder->auto(new Attr\Model(), [$genericClass])
                            : null
                    ),
                    'page' => new Schema(
                        type: SchemaType::INTEGER
                    ),
                    'page_size' => new Schema(
                        type: SchemaType::INTEGER
                    ),
                    'pages' => new Schema(
                        type: SchemaType::INTEGER
                    ),
                    'total' => new Schema(
                        type: SchemaType::INTEGER
                    )
                ]
            )
        );
    }

    /**
     * Describes forward continuation fields without adding a total count to the response.
     *
     * @param SchemaBuilder $builder
     * @param string|null $genericClass
     *
     * @return Response
     * @throws OpenAPIExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public static function buildCursorPage(
        SchemaBuilder $builder,
        ?string $genericClass
    ): Response
    {
        return self::response(new Schema(
            type: SchemaType::OBJECT,
            properties: [
                'items' => new Schema(type: SchemaType::ARRAY, items: $genericClass !== null ? $builder->auto(new Attr\Model(), [$genericClass]) : null),
                'next_cursor' => new Schema(type: SchemaType::STRING, nullable: true),
                'has_more' => new Schema(type: SchemaType::BOOLEAN)
            ],
            required: ['items', 'next_cursor', 'has_more']
        ));
    }

    /**
     * Base response object.
     *
     * @param Schema $schema
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    private static function response(Schema $schema): Response
    {
        return new Response(
            content: [
                'application/json' => new MediaType($schema)
            ]
        );
    }
}
