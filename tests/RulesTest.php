<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Validate\Rules;

final class RulesTest extends TestCase
{
    // --- isEmail ---

    public function testIsEmailValid(): void
    {
        self::assertTrue(Rules::isEmail('user@example.com'));
        self::assertTrue(Rules::isEmail('user+tag@sub.example.org'));
    }

    public function testIsEmailInvalid(): void
    {
        self::assertFalse(Rules::isEmail('not-an-email'));
        self::assertFalse(Rules::isEmail('@missing-local.com'));
        self::assertFalse(Rules::isEmail(''));
    }

    // --- isUrl ---

    public function testIsUrlValid(): void
    {
        self::assertTrue(Rules::isUrl('https://example.com'));
        self::assertTrue(Rules::isUrl('http://example.com/path?q=1'));
    }

    public function testIsUrlInvalid(): void
    {
        self::assertFalse(Rules::isUrl('not a url'));
        self::assertFalse(Rules::isUrl(''));
    }

    public function testIsUrlRequireHttps(): void
    {
        self::assertTrue(Rules::isUrl('https://example.com', requireHttps: true));
        self::assertFalse(Rules::isUrl('http://example.com', requireHttps: true));
    }

    // --- isUuid ---

    public function testIsUuidValid(): void
    {
        self::assertTrue(Rules::isUuid('550e8400-e29b-41d4-a716-446655440000'));
        self::assertTrue(Rules::isUuid('550E8400-E29B-41D4-A716-446655440000')); // uppercase
    }

    public function testIsUuidInvalid(): void
    {
        self::assertFalse(Rules::isUuid('not-a-uuid'));
        self::assertFalse(Rules::isUuid('550e8400-e29b-41d4-a716-44665544000'));  // too short
        self::assertFalse(Rules::isUuid(''));
    }

    // --- isIp / isIpv4 / isIpv6 ---

    public function testIsIpv4(): void
    {
        self::assertTrue(Rules::isIp('192.168.1.1'));
        self::assertTrue(Rules::isIpv4('192.168.1.1'));
        self::assertFalse(Rules::isIpv4('999.999.999.999'));
    }

    public function testIsIpv6(): void
    {
        self::assertTrue(Rules::isIp('::1'));
        self::assertTrue(Rules::isIpv6('2001:db8::1'));
        self::assertFalse(Rules::isIpv6('192.168.1.1'));
    }

    // --- minLength / maxLength ---

    public function testMinLength(): void
    {
        self::assertTrue(Rules::minLength('hello', 3));
        self::assertTrue(Rules::minLength('hello', 5));
        self::assertFalse(Rules::minLength('hi', 3));
    }

    public function testMaxLength(): void
    {
        self::assertTrue(Rules::maxLength('hi', 5));
        self::assertTrue(Rules::maxLength('hello', 5));
        self::assertFalse(Rules::maxLength('toolong', 5));
    }

    public function testLengthIsMultibyteAware(): void
    {
        self::assertTrue(Rules::minLength('äöü', 3));  // 3 chars, not 6 bytes
        self::assertTrue(Rules::maxLength('äöü', 3));
    }

    // --- matches ---

    public function testMatchesValid(): void
    {
        self::assertTrue(Rules::matches('abc123', '/^[a-z0-9]+$/'));
        self::assertTrue(Rules::matches('test@example.com', '/@/'));
    }

    public function testMatchesInvalid(): void
    {
        self::assertFalse(Rules::matches('ABC', '/^[a-z]+$/'));
    }

    // --- between ---

    public function testBetweenInclusive(): void
    {
        self::assertTrue(Rules::between(5, 1, 10));
        self::assertTrue(Rules::between(1, 1, 10));   // lower bound
        self::assertTrue(Rules::between(10, 1, 10));  // upper bound
        self::assertFalse(Rules::between(0, 1, 10));
        self::assertFalse(Rules::between(11, 1, 10));
    }

    public function testBetweenWithFloats(): void
    {
        self::assertTrue(Rules::between(3.14, 0.0, 5.0));
        self::assertFalse(Rules::between(5.01, 0.0, 5.0));
    }

    // --- isPositive / isNegative ---

    public function testIsPositive(): void
    {
        self::assertTrue(Rules::isPositive(1));
        self::assertTrue(Rules::isPositive(0.01));
        self::assertFalse(Rules::isPositive(0));
        self::assertFalse(Rules::isPositive(-1));
    }

    public function testIsNegative(): void
    {
        self::assertTrue(Rules::isNegative(-1));
        self::assertTrue(Rules::isNegative(-0.01));
        self::assertFalse(Rules::isNegative(0));
        self::assertFalse(Rules::isNegative(1));
    }

    // --- notEmpty ---

    public function testNotEmpty(): void
    {
        self::assertTrue(Rules::notEmpty('hello'));
        self::assertTrue(Rules::notEmpty(0));
        self::assertTrue(Rules::notEmpty(false));
        self::assertTrue(Rules::notEmpty([0]));
    }

    public function testNotEmptyFalsy(): void
    {
        self::assertFalse(Rules::notEmpty(null));
        self::assertFalse(Rules::notEmpty(''));
        self::assertFalse(Rules::notEmpty([]));
    }

    // --- isDate ---

    public function testIsDateValid(): void
    {
        self::assertTrue(Rules::isDate('2026-04-23'));
        self::assertTrue(Rules::isDate('2000-01-01'));
    }

    public function testIsDateInvalid(): void
    {
        self::assertFalse(Rules::isDate('2023-02-30')); // day out of range
        self::assertFalse(Rules::isDate('not-a-date'));
        self::assertFalse(Rules::isDate(''));
    }

    public function testIsDateCustomFormat(): void
    {
        self::assertTrue(Rules::isDate('23.04.2026', 'd.m.Y'));
        self::assertFalse(Rules::isDate('2026-04-23', 'd.m.Y'));
    }
}
