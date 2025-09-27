<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;
use Raxos\Contract\OpenAPI\AttributeInterface;
use Raxos\OpenAPI\Enum\In;

/**
 * Class Parameter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Definition
 * @since 1.7.0
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class Parameter implements AttributeInterface
{

    /**
     * Parameter conDefinitionor.
     *
     * @param string $name
     * @param In $in
     * @param string|null $description
     * @param bool $required
     * @param bool $deprecated
     * @param bool $allowEmptyValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $name,
        public In $in,
        public ?string $description = null,
        public bool $required = false,
        public bool $deprecated = false,
        public bool $allowEmptyValue = false
    ) {}

}
