<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum NumberFormat
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.8.0
 */
enum NumberFormat: string
{
    case DOUBLE = 'double';
    case FLOAT = 'float';
    case INT32 = 'int32';
    case INT64 = 'int64';
}
