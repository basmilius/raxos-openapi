<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Error;

use Raxos\Contract\OpenAPI\OpenAPIExceptionInterface;
use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class ReflectionErrorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Error
 * @since 2.0.0
 */
final class ReflectionErrorException extends Exception implements OpenAPIExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * ReflectionErrorException constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ReflectionException $err
    )
    {
        parent::__construct(
            'openapi_reflection',
            'Reflection failed.',
            previous: $this->err
        );
    }

}
