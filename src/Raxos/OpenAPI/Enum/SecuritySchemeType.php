<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum SecuritySchemeType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.7.0
 */
enum SecuritySchemeType: string
{
    case BASIC = 'basic';
    case BEARER = 'bearer';
}
