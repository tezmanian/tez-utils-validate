<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Validate\TypeCast;

final class TypeCastTest extends TestCase
{
    // --- toInt ---

    public function testToIntFromInt(): void
    {
        self::assertSame(42, TypeCast::toInt(42));
        self::assertSame(-7, TypeCast::toInt(-7));
        self::assertSame(0, TypeCast::toInt(0));
    }

    public function testToIntFromExactFloat(): void
    {
        self::assertSame(3, TypeCast::toInt(3.0));
        self::assertSame(-5, TypeCast::toInt(-5.0));
    }

    public function testToIntFromNumericString(): void
    {
        self::assertSame(42, TypeCast::toInt('42'));
        self::assertSame(-7, TypeCast::toInt('-7'));
        self::assertSame(0, TypeCast::toInt('0'));
    }

    public function testToIntThrowsOnFractionalFloat(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toInt(3.7);
    }

    public function testToIntThrowsOnNonNumericString(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toInt('42abc');
    }

    public function testToIntThrowsOnNull(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toInt(null);
    }

    public function testToIntThrowsOnBool(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toInt(true);
    }

    // --- toFloat ---

    public function testToFloatFromFloat(): void
    {
        self::assertSame(3.14, TypeCast::toFloat(3.14));
    }

    public function testToFloatFromInt(): void
    {
        self::assertSame(42.0, TypeCast::toFloat(42));
    }

    public function testToFloatFromNumericString(): void
    {
        self::assertSame(3.14, TypeCast::toFloat('3.14'));
        self::assertSame(42.0, TypeCast::toFloat('42'));
    }

    public function testToFloatThrowsOnNonNumericString(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toFloat('3.14xyz');
    }

    public function testToFloatThrowsOnNull(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toFloat(null);
    }

    // --- toString ---

    public function testToStringFromString(): void
    {
        self::assertSame('hello', TypeCast::toString('hello'));
        self::assertSame('', TypeCast::toString(''));
    }

    public function testToStringFromInt(): void
    {
        self::assertSame('42', TypeCast::toString(42));
    }

    public function testToStringFromFloat(): void
    {
        self::assertSame('3.14', TypeCast::toString(3.14));
    }

    public function testToStringThrowsOnNull(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toString(null);
    }

    public function testToStringThrowsOnBool(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toString(true);
    }

    // --- toBool ---

    public function testToBoolFromBool(): void
    {
        self::assertTrue(TypeCast::toBool(true));
        self::assertFalse(TypeCast::toBool(false));
    }

    public function testToBoolFromInt(): void
    {
        self::assertTrue(TypeCast::toBool(1));
        self::assertFalse(TypeCast::toBool(0));
    }

    public function testToBoolFromTruthyStrings(): void
    {
        foreach (['true', 'yes', 'on', '1', 'TRUE', 'YES'] as $v) {
            self::assertTrue(TypeCast::toBool($v), "Expected true for '{$v}'");
        }
    }

    public function testToBoolFromFalsyStrings(): void
    {
        foreach (['false', 'no', 'off', '0', '', 'FALSE', 'NO'] as $v) {
            self::assertFalse(TypeCast::toBool($v), "Expected false for '{$v}'");
        }
    }

    public function testToBoolThrowsOnAmbiguousString(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toBool('maybe');
    }

    public function testToBoolThrowsOnNull(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TypeCast::toBool(null);
    }

    // --- nullable variants ---

    public function testNullableReturnsNullOnNull(): void
    {
        self::assertNull(TypeCast::toNullableInt(null));
        self::assertNull(TypeCast::toNullableFloat(null));
        self::assertNull(TypeCast::toNullableString(null));
        self::assertNull(TypeCast::toNullableBool(null));
    }

    public function testNullableConvertsNonNull(): void
    {
        self::assertSame(42, TypeCast::toNullableInt('42'));
        self::assertSame(3.14, TypeCast::toNullableFloat('3.14'));
        self::assertSame('hello', TypeCast::toNullableString('hello'));
        self::assertTrue(TypeCast::toNullableBool('true'));
    }
}
