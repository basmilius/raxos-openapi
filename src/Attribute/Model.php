<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;

/**
 * Class Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 1.8.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Model extends Schema {}
