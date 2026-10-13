<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Validate;

/**
 * Strict type coercion — throws UnexpectedValueException when a value
 * cannot be unambiguously converted, unlike PHP built-ins (intval, settype)
 * which silently truncate or guess.
 */
final class TypeCast
{
    /**
     * Converts a value to int.
     * Accepts: int (as-is), float with exact integer value (e.g. 3.0 → 3),
     * purely numeric integer string (e.g. '42', '-7').
     *
     * @throws \UnexpectedValueException for any other input
     */
    public static function toInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (floor($value) !== $value) {
                throw new \UnexpectedValueException(
                    sprintf('Cannot convert float %s to int without data loss.', $value),
                );
            }

            return (int) $value;
        }

        if (is_string($value) && preg_match('/^[+-]?\d+$/', $value)) {
            return (int) $value;
        }

        throw new \UnexpectedValueException(
            sprintf('Cannot convert %s to int.', get_debug_type($value)),
        );
    }

    /**
     * Converts a value to float.
     * Accepts: float (as-is), int (promoted), numeric string.
     *
     * @throws \UnexpectedValueException for any other input
     */
    public static function toFloat(mixed $value): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        throw new \UnexpectedValueException(
            sprintf('Cannot convert %s to float.', get_debug_type($value)),
        );
    }

    /**
     * Converts a value to string.
     * Accepts: string (as-is), int, float.
     *
     * @throws \UnexpectedValueException for null, bool, array, object, and other types
     */
    public static function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        throw new \UnexpectedValueException(
            sprintf('Cannot convert %s to string.', get_debug_type($value)),
        );
    }

    /**
     * Converts a value to bool using explicit coercion rules.
     *
     * Truthy:  true, 1, "true", "yes", "on", "1"
     * Falsy:   false, 0, "false", "no", "off", "0", ""
     *
     * @throws \UnexpectedValueException for any other input
     */
    public static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1) {
            return true;
        }

        if ($value === 0) {
            return false;
        }

        if (is_string($value)) {
            $lower = strtolower($value);

            if (in_array($lower, ['true', 'yes', 'on', '1'], true)) {
                return true;
            }

            if (in_array($lower, ['false', 'no', 'off', '0', ''], true)) {
                return false;
            }
        }

        throw new \UnexpectedValueException(
            sprintf('Cannot convert %s to bool.', get_debug_type($value)),
        );
    }

    /** Converts to int, or returns null if the value is null. */
    public static function toNullableInt(mixed $value): ?int
    {
        return $value === null ? null : self::toInt($value);
    }

    /** Converts to float, or returns null if the value is null. */
    public static function toNullableFloat(mixed $value): ?float
    {
        return $value === null ? null : self::toFloat($value);
    }

    /** Converts to string, or returns null if the value is null. */
    public static function toNullableString(mixed $value): ?string
    {
        return $value === null ? null : self::toString($value);
    }

    /** Converts to bool, or returns null if the value is null. */
    public static function toNullableBool(mixed $value): ?bool
    {
        return $value === null ? null : self::toBool($value);
    }
}
