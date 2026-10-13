<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Validate;

/**
 * Applies a validation callable to every item in an array.
 * On the first failure the caught exception is re-thrown with the index path
 * prepended to the message (e.g. "items[2]: Amount must be positive").
 */
final class Each
{
    /**
     * Validates every item in $items by calling $rule($item, $index).
     * $rule should throw a Throwable on failure or return normally on success.
     * Fails fast — stops at the first failing item.
     *
     * @param array<mixed> $items
     * @param callable(mixed, int|string): void $rule
     * @throws \Throwable the original exception type with path prepended to the message
     */
    public static function validate(
        array $items,
        callable $rule,
        string $path = 'items',
    ): void {
        foreach ($items as $index => $item) {
            try {
                $rule($item, $index);
            } catch (\Throwable $e) {
                $message = sprintf('%s[%s]: %s', $path, (string) $index, $e->getMessage());
                $class   = $e::class;

                // Re-throw using the same exception type so callers can catch it by class.
                // Most PHP exceptions accept (string $message, int $code, Throwable $previous).
                throw new $class($message, (int) $e->getCode(), $e);
            }
        }
    }
}
