<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum SchemaType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.8.0
 */
enum SchemaType: string
{
    case ARRAY = 'array';
    case BOOLEAN = 'boolean';
    case INTEGER = 'integer';
    case NULL = 'null';
    case NUMBER = 'number';
    case OBJECT = 'object';
    case STRING = 'string';
}
