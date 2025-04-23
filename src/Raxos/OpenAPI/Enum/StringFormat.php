<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum StringFormat
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.8.0
 */
enum StringFormat: string
{
    case BINARY = 'binary';
    case BYTE = 'byte';
    case DATE = 'date';
    case DATE_TIME = 'date-time';
    case EMAIL = 'email';
    case HOSTNAME = 'hostname';
    case IPV4 = 'ipv4';
    case IPV6 = 'ipv6';
    case PASSWORD = 'password';
    case TIME = 'time';
    case URI = 'uri';
    case URI_REFERENCE = 'uri-reference';
    case UUID = 'uuid';
}
