<?php

declare(strict_types=1);

namespace Tez\Utils\Validate;

/**
 * Combines boolean predicates with AND / OR / NONE logic.
 * Each method returns a Closure that accepts a value and returns bool,
 * so the result can be passed directly to Guard::that().
 */
final class Composite
{
    /**
     * Returns a predicate that passes only when every rule returns true (AND).
     * Short-circuits on the first failure.
     *
     * @param callable(mixed): bool ...$rules
     * @return \Closure(mixed): bool
     */
    public static function all(callable ...$rules): \Closure
    {
        return static function (mixed $value) use ($rules): bool {
            foreach ($rules as $rule) {
                if (!$rule($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Returns a predicate that passes when at least one rule returns true (OR).
     * Short-circuits on the first success.
     *
     * @param callable(mixed): bool ...$rules
     * @return \Closure(mixed): bool
     */
    public static function any(callable ...$rules): \Closure
    {
        return static function (mixed $value) use ($rules): bool {
            foreach ($rules as $rule) {
                if ($rule($value)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Returns a predicate that passes only when every rule returns false (NOR).
     * Short-circuits on the first success.
     *
     * @param callable(mixed): bool ...$rules
     * @return \Closure(mixed): bool
     */
    public static function none(callable ...$rules): \Closure
    {
        return static function (mixed $value) use ($rules): bool {
            foreach ($rules as $rule) {
                if ($rule($value)) {
                    return false;
                }
            }

            return true;
        };
    }
}
