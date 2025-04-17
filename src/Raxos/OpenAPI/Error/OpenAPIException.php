<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Error;

use Raxos\Foundation\Error\{ExceptionId, RaxosException};
use ReflectionException;

/**
 * Class OpenAPIException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Error
 * @since 1.7.0
 */
final class OpenAPIException extends RaxosException
{

    /**
     * Returns the exception for when reflection failed.
     *
     * @param ReflectionException $err
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function reflection(ReflectionException $err): self
    {
        return new self(
            ExceptionId::guess(),
            'openapi_reflection',
            'Reflection failed.',
            $err
        );
    }

}
