<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Validate\Composite;
use Tezmanian\Utils\Validate\Rules;

final class CompositeTest extends TestCase
{
    // --- all ---

    public function testAllReturnsTrueWhenEveryRulePasses(): void
    {
        $check = Composite::all(
            fn($v) => is_string($v) && Rules::minLength($v, 3),
            fn($v) => is_string($v) && Rules::maxLength($v, 10),
            fn($v) => is_string($v) && Rules::matches($v, '/^[a-z]+$/'),
        );

        self::assertTrue($check('hello'));
    }

    public function testAllReturnsFalseWhenAnyRuleFails(): void
    {
        $check = Composite::all(
            fn($v) => is_string($v) && Rules::minLength($v, 3),
            fn($v) => is_string($v) && Rules::maxLength($v, 4),
        );

        self::assertFalse($check('toolong'));
    }

    public function testAllShortCircuitsOnFirstFailure(): void
    {
        $called = 0;
        $check  = Composite::all(
            fn() => false,
            function () use (&$called) {
                $called++;

                return true;
            },
        );

        $check('x');

        self::assertSame(0, $called);
    }

    public function testAllWithNoRulesReturnsTrue(): void
    {
        $check = Composite::all();
        self::assertTrue($check('anything'));
    }

    // --- any ---

    public function testAnyReturnsTrueWhenOneRulePasses(): void
    {
        $check = Composite::any(
            fn($v) => is_string($v) && Rules::isEmail($v),
            fn($v) => is_string($v) && Rules::isUuid($v),
        );

        self::assertTrue($check('user@example.com'));
    }

    public function testAnyReturnsFalseWhenNoRulePasses(): void
    {
        $check = Composite::any(
            fn($v) => is_string($v) && Rules::isEmail($v),
            fn($v) => is_string($v) && Rules::isUuid($v),
        );

        self::assertFalse($check('neither'));
    }

    public function testAnyShortCircuitsOnFirstSuccess(): void
    {
        $called = 0;
        $check  = Composite::any(
            fn() => true,
            function () use (&$called) {
                $called++;

                return false;
            },
        );

        $check('x');

        self::assertSame(0, $called);
    }

    public function testAnyWithNoRulesReturnsFalse(): void
    {
        $check = Composite::any();
        self::assertFalse($check('anything'));
    }

    // --- none ---

    public function testNoneReturnsTrueWhenNoRuleMatches(): void
    {
        $isReserved = Composite::none(
            fn($v) => $v === 'admin',
            fn($v) => $v === 'root',
        );

        self::assertTrue($isReserved('alice'));
    }

    public function testNoneReturnsFalseWhenAnyRuleMatches(): void
    {
        $isReserved = Composite::none(
            fn($v) => $v === 'admin',
            fn($v) => $v === 'root',
        );

        self::assertFalse($isReserved('admin'));
    }

    public function testNoneShortCircuitsOnFirstMatch(): void
    {
        $called = 0;
        $check  = Composite::none(
            fn() => true,
            function () use (&$called) {
                $called++;

                return false;
            },
        );

        $check('x');

        self::assertSame(0, $called);
    }

    public function testNoneWithNoRulesReturnsTrue(): void
    {
        $check = Composite::none();
        self::assertTrue($check('anything'));
    }

    // --- integration with Guard ---

    public function testAllComposesWithGuard(): void
    {
        $isStrongPassword = Composite::all(
            fn($v) => is_string($v) && Rules::minLength($v, 8),
            fn($v) => is_string($v) && Rules::matches($v, '/[A-Z]/'),
            fn($v) => is_string($v) && Rules::matches($v, '/[0-9]/'),
        );

        self::assertTrue($isStrongPassword('Secret42!'));
        self::assertFalse($isStrongPassword('weak'));
    }
}
