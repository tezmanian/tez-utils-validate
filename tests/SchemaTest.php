<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Validate\Guard;
use Tezmanian\Utils\Validate\Schema;

final class SchemaTest extends TestCase
{
    public function testValidDataPassesWithoutException(): void
    {
        $this->expectNotToPerformAssertions();

        Schema::validate([
            'name' => 'Alice',
            'age'  => 28,
            'active' => true,
        ], [
            'name'   => 'string',
            'age'    => 'int',
            'active' => 'bool',
        ]);
    }

    public function testThrowsWhenRequiredKeyIsMissing(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/name.*required/');

        Schema::validate([], ['name' => 'string']);
    }

    public function testThrowsOnWrongType(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/age.*expected int.*got string/');

        Schema::validate(['age' => 'not-an-int'], ['age' => 'int']);
    }

    public function testOptionalKeyCanBeAbsent(): void
    {
        $this->expectNotToPerformAssertions();
        Schema::validate(['name' => 'Alice'], ['name' => 'string', 'email' => '?string']);
    }

    public function testOptionalKeyCanBeNull(): void
    {
        $this->expectNotToPerformAssertions();
        Schema::validate(['name' => 'Alice', 'email' => null], ['name' => 'string', 'email' => '?string']);
    }

    public function testOptionalKeyWithWrongTypeThrows(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        Schema::validate(['email' => 42], ['email' => '?string']);
    }

    public function testNestedSchemaIsValidated(): void
    {
        $this->expectNotToPerformAssertions();

        Schema::validate([
            'user' => ['name' => 'Alice', 'age' => 28],
        ], [
            'user' => ['name' => 'string', 'age' => 'int'],
        ]);
    }

    public function testNestedSchemaFailsWithDotNotationPath(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/address\.zip/');

        Schema::validate([
            'address' => ['city' => 'Berlin', 'zip' => 12345],
        ], [
            'address' => ['city' => 'string', 'zip' => 'string'],
        ]);
    }

    public function testCallableRuleIsApplied(): void
    {
        $this->expectNotToPerformAssertions();

        Schema::validate(['score' => 75], [
            'score' => fn($v) => Guard::inRange($v, 0, 100),
        ]);
    }

    public function testCallableRuleFailureIncludesPath(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/score:/');

        Schema::validate(['score' => 150], [
            'score' => fn($v) => Guard::inRange($v, 0, 100),
        ]);
    }

    public function testMixedTypeAcceptsAnything(): void
    {
        $this->expectNotToPerformAssertions();

        Schema::validate(
            ['data' => ['anything', 42, true, null]],
            ['data' => 'mixed'],
        );
    }

    public function testNestedOptionalField(): void
    {
        $this->expectNotToPerformAssertions();

        Schema::validate([
            'address' => ['city' => 'Berlin'],
        ], [
            'address' => ['city' => 'string', 'country' => '?string'],
        ]);
    }

    public function testPathIsReportedCorrectlyForDeepNesting(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/user\.profile\.displayName/');

        Schema::validate([
            'user' => [
                'profile' => ['displayName' => 123],
            ],
        ], [
            'user' => [
                'profile' => ['displayName' => 'string'],
            ],
        ]);
    }

    public function testUnknownTypeStringThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Schema::validate(['x' => 'value'], ['x' => 'uuid']);
    }
}
