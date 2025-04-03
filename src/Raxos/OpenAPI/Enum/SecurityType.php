<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Enum;

/**
 * Enum SecurityType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Enum
 * @since 1.7.0
 */
enum SecurityType: string
{
    case API_KEY = 'apiKey';
    case HTTP = 'http';
    case OAUTH2 = 'oauth2';
    case OPENID_CONNECT = 'openIdConnect';
}
