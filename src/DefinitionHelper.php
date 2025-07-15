<?php
declare(strict_types=1);

namespace Raxos\OpenAPI;

use Generator;
use function is_int;
use function is_string;

/**
 * Class DefinitionHelper
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OpenAPI
 * @since 1.7.0
 * @internal
 * @private
 */
final class DefinitionHelper
{

    /**
     * Checks if the given value is not empty.
     *
     * @param mixed $value
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function isNotEmpty(mixed $value): bool
    {
        return !empty($value);
    }

    /**
     * Checks if the given value is not null.
     *
     * @param mixed $value
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function isNotNull(mixed $value): bool
    {
        return $value !== null;
    }

    /**
     * Normalize the security array.
     *
     * @param array $security
     *
     * @return Generator<string, array>
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public static function normalizeSecurity(array $security): Generator
    {
        foreach ($security as $key => $value) {
            if (is_int($key) && is_string($value)) {
                yield [$value => []];
                continue;
            }

            yield [$key => $value];
        }
    }

}
