<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;

/**
 * Class Required
 *
 * Overrides request requiredness, including conditional validation rules.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 3.3.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Required
{

    /**
     * Overrides inferred input requiredness, including rules that depend on runtime context.
     *
     * @param bool $required
     *
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function __construct(public bool $required = true) {}

}
