<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;
use Raxos\Http\HttpResponseCode;
use Raxos\OpenAPI\Contract\AttributeInterface;

/**
 * Class Response
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.7.0
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Response implements AttributeInterface
{

    /**
     * Response constructor.
     *
     * @param HttpResponseCode $code
     * @param string|null $description
     * @param class-string|null $model
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public HttpResponseCode $code,
        public ?string $description = null,
        public ?string $model = null
    ) {}

}
