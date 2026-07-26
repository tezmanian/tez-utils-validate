<?php

declare(strict_types=1);

namespace Tez\Utils\Validate;

final class Guard
{
    /**
     * Asserts that a value is not null. Returns the value for direct assignment.
     *
     * @template T
     * @param T|null $value
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     * @return T
     */
    public static function notNull(
        mixed $value,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): mixed {
        if ($value === null) {
            self::doThrow($exception, 'Value must not be null.');
        }

        return $value;
    }

    /**
     * Asserts that a value is not empty (null, '', '0', 0, false, []). Returns the value.
     *
     * @template T
     * @param T $value
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     * @return T
     */
    public static function notEmpty(
        mixed $value,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): mixed {
        if (empty($value)) {
            self::doThrow($exception, 'Value must not be empty.');
        }

        return $value;
    }

    /**
     * Asserts that a numeric value lies within [$min, $max] (inclusive). Returns the value.
     *
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     */
    public static function inRange(
        int|float $value,
        int|float $min,
        int|float $max,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): int|float {
        if ($value < $min || $value > $max) {
            self::doThrow($exception, sprintf('Value %s is not in range [%s, %s].', $value, $min, $max));
        }

        return $value;
    }

    /**
     * Asserts that a value is an instance of the given class. Returns the typed value.
     *
     * @template T of object
     * @param class-string<T> $class
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     * @return T
     */
    public static function instanceOf(
        mixed $value,
        string $class,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): object {
        if (!($value instanceof $class)) {
            self::doThrow($exception, sprintf('Value must be an instance of %s.', $class));
        }

        return $value;
    }

    /**
     * Asserts that a boolean value is true. Returns true.
     *
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     */
    public static function isTrue(
        bool $value,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): bool {
        if ($value !== true) {
            self::doThrow($exception, 'Value must be true.');
        }

        return true;
    }

    /**
     * Asserts that a boolean value is false. Returns false.
     *
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     */
    public static function isFalse(
        bool $value,
        string|\Throwable|\Closure $exception = \RuntimeException::class,
    ): bool {
        if ($value !== false) {
            self::doThrow($exception, 'Value must be false.');
        }

        return false;
    }

    /**
     * Asserts a custom boolean condition using a Closure-based exception factory.
     *
     * @param \Closure(): \Throwable $exception
     */
    public static function that(
        bool $condition,
        \Closure $exception,
    ): bool {
        if (!$condition) {
            self::doThrow($exception, '');
        }

        return true;
    }

    /**
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     */
    private static function doThrow(string|\Throwable|\Closure $exception, string $defaultMessage): never
    {
        if ($exception instanceof \Throwable) {
            throw $exception;
        }

        if ($exception instanceof \Closure) {
            throw $exception();
        }

        throw new $exception($defaultMessage);
    }

    /**
     * Asserts that a value is numeric. Returns the value for direct assignment.
     *
     * @template T
     * @param T|null $value
     * @param class-string<\Throwable>|\Throwable|\Closure(): \Throwable $exception
     * @throws \Throwable
     * @return T
     */
    public static function isNumeric(
        mixed $value,
        string|\Throwable|\Closure $exception = \InvalidArgumentException::class,
    ): mixed {
        if (!is_numeric($value)) {
            self::doThrow($exception, 'Expected a numeric string.');
        }

        return $value;
    }
}
