<?php
declare(strict_types=1);

namespace Raxos\OpenAPI\Attribute;

use Attribute;
use Raxos\Database\Orm\Model;
use Raxos\Search\Attribute\Filter;

/**
 * Class FilterParams
 *
 * Documents the structured search/filter query parameters of an endpoint by
 * reflecting the `#[Filter]` attributes declared on the
 * given model. Each filter that implements `StructuredFilterInterface` contributes
 * one or more query parameters via its `describe()` method.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI\Attribute
 * @since 2.2.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class FilterParams
{

    /**
     * FilterParams constructor.
     *
     * @param class-string<Model> $model
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public string $model
    ) {}

}
