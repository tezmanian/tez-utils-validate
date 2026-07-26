<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Validate\Guard;

final class GuardTest extends TestCase
{
    // --- notNull ---

    public function testNotNullPassesAndReturnsValue(): void
    {
        self::assertSame('hello', Guard::notNull('hello'));
        self::assertSame(0, Guard::notNull(0));
        self::assertSame(false, Guard::notNull(false));
    }

    public function testNotNullThrowsDefaultExceptionOnNull(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::notNull(null);
    }

    public function testNotNullThrowsCustomExceptionClass(): void
    {
        $this->expectException(\LogicException::class);

        Guard::notNull(null, \LogicException::class);
    }

    public function testNotNullThrowsExceptionInstance(): void
    {
        $this->expectException(\OverflowException::class);
        $this->expectExceptionMessage('custom message');

        Guard::notNull(null, new \OverflowException('custom message'));
    }

    public function testNotNullThrowsExceptionFromClosure(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('from closure');

        Guard::notNull(null, fn() => new \DomainException('from closure'));
    }

    // --- notEmpty ---

    public function testNotEmptyPassesAndReturnsValue(): void
    {
        self::assertSame('hello', Guard::notEmpty('hello'));
        self::assertSame([1], Guard::notEmpty([1]));
        self::assertSame(1, Guard::notEmpty(1));
    }

    public function testNotEmptyThrowsOnEmptyString(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::notEmpty('');
    }

    public function testNotEmptyThrowsOnEmptyArray(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::notEmpty([]);
    }

    public function testNotEmptyThrowsOnNull(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::notEmpty(null);
    }

    public function testNotEmptyThrowsCustomException(): void
    {
        $this->expectException(\LogicException::class);

        Guard::notEmpty('', \LogicException::class);
    }

    // --- inRange ---

    public function testInRangePassesAndReturnsValue(): void
    {
        self::assertSame(5, Guard::inRange(5, min: 1, max: 10));
        self::assertSame(1, Guard::inRange(1, min: 1, max: 10));
        self::assertSame(10, Guard::inRange(10, min: 1, max: 10));
    }

    public function testInRangePassesForFloat(): void
    {
        self::assertSame(3.5, Guard::inRange(3.5, min: 0.0, max: 5.0));
    }

    public function testInRangeThrowsBelowMin(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::inRange(0, min: 1, max: 10);
    }

    public function testInRangeThrowsAboveMax(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::inRange(11, min: 1, max: 10);
    }

    public function testInRangeThrowsCustomException(): void
    {
        $this->expectException(\OverflowException::class);

        Guard::inRange(200, min: 0, max: 150, exception: \OverflowException::class);
    }

    // --- instanceOf ---

    public function testInstanceOfPassesAndReturnsTypedValue(): void
    {
        $dt     = new \DateTime();
        $result = Guard::instanceOf($dt, \DateTime::class);

        self::assertSame($dt, $result);
    }

    public function testInstanceOfThrowsOnWrongType(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::instanceOf(new \stdClass(), \DateTime::class);
    }

    public function testInstanceOfThrowsCustomException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        Guard::instanceOf('not an object', \DateTime::class, \UnexpectedValueException::class);
    }

    // --- isTrue ---

    public function testIsTruePassesAndReturnsTrue(): void
    {
        self::assertTrue(Guard::isTrue(true));
    }

    public function testIsTrueThrowsOnFalse(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::isTrue(false);
    }

    public function testIsTrueThrowsCustomException(): void
    {
        $this->expectException(\LogicException::class);

        Guard::isTrue(false, \LogicException::class);
    }

    // --- isFalse ---

    public function testIsFalsePassesAndReturnsFalse(): void
    {
        self::assertFalse(Guard::isFalse(false));
    }

    public function testIsFalseThrowsOnTrue(): void
    {
        $this->expectException(\RuntimeException::class);

        Guard::isFalse(true);
    }

    // --- that ---

    public function testThatPassesOnTrueCondition(): void
    {
        /** @var int $value */
        $value = 1;
        self::assertTrue(Guard::that($value > 0, fn() => new \DomainException('fail')));
    }

    public function testThatThrowsOnFalseCondition(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Must be positive');

        /** @var int $value */
        $value = -1;
        Guard::that($value > 0, fn() => new \DomainException('Must be positive'));
    }

    // --- no InvalidArgumentException in default path ---

    public function testDefaultExceptionIsNotInvalidArgumentException(): void
    {
        try {
            Guard::notNull(null);
        } catch (\Throwable $e) {
            self::assertNotInstanceOf(\InvalidArgumentException::class, $e);

            return;
        }

        self::fail('Expected exception was not thrown.');
    }

    // --- isNumeric ---

    public function testIsNumericPassesIntegerString(): void
    {
        self::assertSame('42', Guard::isNumeric('42'));
    }

    public function testIsNumericPassesDecimalString(): void
    {
        self::assertSame('3.14', Guard::isNumeric('3.14'));
    }

    public function testIsNumericPassesNegativeString(): void
    {
        self::assertSame('-99.5', Guard::isNumeric('-99.5'));
    }

    public function testIsNumericPassesIntegerType(): void
    {
        self::assertSame(42, Guard::isNumeric(42));
    }

    public function testIsNumericPassesFloatType(): void
    {
        self::assertSame(3.14, Guard::isNumeric(3.14));
    }

    public function testIsNumericThrowsDefaultExceptionOnNonNumericString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Guard::isNumeric('not-a-number');
    }

    public function testIsNumericThrowsOnNull(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Guard::isNumeric(null);
    }

    public function testIsNumericThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Guard::isNumeric('');
    }

    public function testIsNumericThrowsOnNonNumericString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Guard::isNumeric('not-a-number');
    }

    public function testIsNumericThrowsCustomExceptionClass(): void
    {
        $this->expectException(\DomainException::class);
        Guard::isNumeric('abc', \DomainException::class);
    }

    public function testIsNumericThrowsExceptionFromClosure(): void
    {
        $this->expectException(\OverflowException::class);
        $this->expectExceptionMessage('not numeric');
        Guard::isNumeric('abc', fn() => new \OverflowException('not numeric'));
    }
}
