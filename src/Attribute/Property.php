<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;

/**
 * Class Property
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.8.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Property extends Schema {}
