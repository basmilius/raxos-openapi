<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;
use Raxos\OpenAPI\Contract\AttributeInterface;

/**
 * Class Hidden
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.7.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Hidden implements AttributeInterface {}
