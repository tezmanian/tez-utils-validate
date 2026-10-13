<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Validate\Each;
use Tezmanian\Utils\Validate\Guard;

final class EachTest extends TestCase
{
    public function testPassesWhenAllItemsAreValid(): void
    {
        $this->expectNotToPerformAssertions();

        Each::validate([1, 2, 3], function (mixed $item): void {
            Guard::isTrue(is_int($item) && $item > 0, \InvalidArgumentException::class);
        });
    }

    public function testThrowsOnFirstInvalidItem(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/items\[1\]/');

        $orders = [
            ['amount' => 50],
            ['amount' => -10],
            ['amount' => 200],
        ];

        Each::validate($orders, function (mixed $order): void {
            /** @var array{amount: int} $order */
            if ($order['amount'] <= 0) {
                throw new \DomainException('Amount must be positive.');
            }
        });
    }

    public function testIndexIsIncludedInMessage(): void
    {
        try {
            Each::validate(['ok', 'ok', 'fail'], function (mixed $item): void {
                if ($item === 'fail') {
                    throw new \RuntimeException('bad value');
                }
            });

            self::fail('Expected exception not thrown.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('items[2]', $e->getMessage());
            self::assertStringContainsString('bad value', $e->getMessage());
        }
    }

    public function testCustomPathIsUsed(): void
    {
        try {
            Each::validate(['a', 'b'], fn() => throw new \RuntimeException('err'), path: 'lines');
            self::fail('Expected exception not thrown.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('lines[', $e->getMessage());
        }
    }

    public function testEmptyArrayPasses(): void
    {
        $this->expectNotToPerformAssertions();
        Each::validate([], fn() => throw new \RuntimeException('should not be called'));
    }

    public function testOriginalExceptionIsAvailableAsPrevious(): void
    {
        $original = new \DomainException('original message');

        try {
            Each::validate(['x'], function () use ($original): void {
                throw $original;
            });
        } catch (\DomainException $e) {
            self::assertSame($original, $e->getPrevious());
        }
    }

    public function testRuleReceivesIndexAsSecondArgument(): void
    {
        $received = [];

        Each::validate(['a', 'b', 'c'], function (mixed $item, int|string $index) use (&$received): void {
            $received[] = $index;
        });

        self::assertSame([0, 1, 2], $received);
    }
}
