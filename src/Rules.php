<?php

declare(strict_types=1);

namespace Tez\Utils\Validate;

/**
 * Stateless boolean predicates for common validation needs.
 * Returns true/false — no exceptions, no side-effects.
 * Designed to work standalone and as input to Guard::that().
 */
final class Rules
{
    /** Returns true if the value is a valid e-mail address. */
    public static function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Returns true if the value is a valid URL.
     * When $requireHttps is true, only https:// URLs are accepted.
     */
    public static function isUrl(string $value, bool $requireHttps = false): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return !$requireHttps || str_starts_with($value, 'https://');
    }

    /** Returns true if the value is a valid UUID (v1–v5, case-insensitive). */
    public static function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
        ) === 1;
    }

    /** Returns true if the value is a valid IPv4 or IPv6 address. */
    public static function isIp(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /** Returns true if the value is a valid IPv4 address. */
    public static function isIpv4(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /** Returns true if the value is a valid IPv6 address. */
    public static function isIpv6(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /** Returns true if the multibyte length of $value is at least $min characters. */
    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    /** Returns true if the multibyte length of $value is at most $max characters. */
    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }

    /** Returns true if the value matches the given regular expression pattern. */
    public static function matches(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /** Returns true if $value is between $min and $max (inclusive). */
    public static function between(int|float $value, int|float $min, int|float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /** Returns true if $value is strictly greater than zero. */
    public static function isPositive(int|float $value): bool
    {
        return $value > 0;
    }

    /** Returns true if $value is strictly less than zero. */
    public static function isNegative(int|float $value): bool
    {
        return $value < 0;
    }

    /** Returns true if the value is not null, not an empty string, and not an empty array. */
    public static function notEmpty(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * Returns true if $value is a valid date string matching the given format.
     * Uses strict validation — '2023-02-30' returns false even though PHP can parse it.
     */
    public static function isDate(string $value, string $format = 'Y-m-d'): bool
    {
        $dt = \DateTimeImmutable::createFromFormat($format, $value);

        return $dt !== false && $dt->format($format) === $value;
    }
}
