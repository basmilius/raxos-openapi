<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum In
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.7.0
 */
enum In: string
{
    case COOKIE = 'cookie';
    case HEADER = 'header';
    case PATH = 'path';
    case QUERY = 'query';
}
