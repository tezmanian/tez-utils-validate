# tez-utils-validate

[![CI](https://github.com/tezmanian/tez-utils-validate/actions/workflows/php.yml/badge.svg)](https://github.com/tezmanian/tez-utils-validate/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/tezmanian/utils-validate)](https://packagist.org/packages/tezmanian/utils-validate)
[![PHP](https://img.shields.io/packagist/php-v/tezmanian/utils-validate)](https://packagist.org/packages/tezmanian/utils-validate)
[![License: MIT](https://img.shields.io/packagist/l/tezmanian/utils-validate)](LICENSE)

PHP validation toolkit with guard assertions, composable rules, schema validation, strict type casting, and array-item validation. PHP 8.3+, no framework dependencies.

## Installation

```bash
composer require tezmanian/utils-validate
```

Package: [`tezmanian/utils-validate`](https://packagist.org/packages/tezmanian/utils-validate) · Namespace: `Tezmanian\Utils\Validate`

## Components

| Class | Purpose |
|-------|---------|
| `Guard` | Assertion helpers that throw on failure and return the value on success |
| `Rules` | Stateless boolean predicates — no exceptions, no side-effects |
| `Composite` | Combines predicates with AND / OR / NONE logic |
| `Schema` | Validates an array against a shape definition |
| `TypeCast` | Strict type coercion that throws instead of silently truncating |
| `Each` | Applies a validation rule to every item in an array |

---

## Guard

Assertions that return the value on success, making them suitable for inline use. Throws `RuntimeException` by default; the exception can be overridden with a class name, an instance, or a closure.

```php
use Tezmanian\Utils\Validate\Guard;

// Basic assertions
$name  = Guard::notNull($request->get('name'));
$email = Guard::notEmpty($request->get('email'));
$score = Guard::inRange($input, min: 0, max: 100);
$user  = Guard::instanceOf($entity, User::class);

// Boolean assertions
Guard::isTrue($user->isActive());
Guard::isFalse($user->isBanned());

// Numeric string
Guard::isNumeric($request->get('amount'));

// Custom predicate
Guard::that($price > 0, fn() => new DomainException('Price must be positive'));
```

Custom exceptions:

```php
Guard::notNull($value, \InvalidArgumentException::class);
Guard::notNull($value, new \DomainException('Value is required'));
Guard::notNull($value, fn() => new \DomainException('Value is required'));
```

---

## Rules

Stateless boolean predicates. Return `true`/`false` — never throw.

```php
use Tezmanian\Utils\Validate\Rules;

Rules::isEmail('user@example.com');          // true
Rules::isUrl('https://example.com');         // true
Rules::isUrl('http://...', requireHttps: true); // false
Rules::isUuid('550e8400-e29b-41d4-...');     // true
Rules::isIp('192.168.1.1');                  // true
Rules::isIpv4('192.168.1.1');               // true
Rules::isIpv6('::1');                        // true

Rules::minLength('hello', 3);               // true
Rules::maxLength('hello', 10);              // true
Rules::matches('abc123', '/^[a-z0-9]+$/'); // true

Rules::between(5, 1, 10);                   // true
Rules::isPositive(1);                        // true
Rules::isNegative(-1);                       // true
Rules::notEmpty('hello');                    // true

Rules::isDate('2026-04-23');                // true
Rules::isDate('23.04.2026', 'd.m.Y');       // true
```

---

## Composite

Combines multiple predicates into one using AND, OR, or NONE logic. Each method returns a `Closure` that can be passed directly to `Guard::that()`.

```php
use Tezmanian\Utils\Validate\Composite;
use Tezmanian\Utils\Validate\Rules;

$isStrongPassword = Composite::all(
    fn($v) => Rules::minLength($v, 8),
    fn($v) => Rules::matches($v, '/[A-Z]/'),
    fn($v) => Rules::matches($v, '/[0-9]/'),
);

$isStrongPassword('Secret42!'); // true
$isStrongPassword('weak');      // false

// OR — passes when at least one rule matches
$isEmailOrUuid = Composite::any(
    fn($v) => Rules::isEmail($v),
    fn($v) => Rules::isUuid($v),
);

// NONE — passes when no rule matches
$isNotReserved = Composite::none(
    fn($v) => $v === 'admin',
    fn($v) => $v === 'root',
);
```

---

## Schema

Validates an array against a shape definition. Fails fast on the first violation and reports the dot-notation path.

**Supported type strings:** `string`, `int`, `float`, `bool`, `array`, `mixed`  
Prefix with `?` to mark a field as optional (absent or `null` is accepted).

```php
use Tezmanian\Utils\Validate\Schema;
use Tezmanian\Utils\Validate\Guard;

Schema::validate($data, [
    'name'    => 'string',
    'age'     => 'int',
    'active'  => 'bool',
    'email'   => '?string',   // optional
    'score'   => fn($v) => Guard::inRange($v, 0, 100), // custom rule
    'address' => [            // nested schema
        'city'    => 'string',
        'country' => '?string',
    ],
]);
```

On failure, an `UnexpectedValueException` is thrown with the dot-notation path:

```
address.zip: expected string, got int
user.profile.displayName: required field missing
```

---

## TypeCast

Strict type coercion — throws `UnexpectedValueException` when a value cannot be unambiguously converted, unlike PHP built-ins that silently truncate or guess.

```php
use Tezmanian\Utils\Validate\TypeCast;

TypeCast::toInt('42');      // 42
TypeCast::toInt(3.0);       // 3
TypeCast::toInt(3.7);       // throws — fractional float

TypeCast::toFloat('3.14');  // 3.14
TypeCast::toString(42);     // '42'

TypeCast::toBool('true');   // true
TypeCast::toBool('yes');    // true
TypeCast::toBool('off');    // false
TypeCast::toBool('maybe');  // throws — ambiguous string

// Nullable variants — pass through null, convert everything else
TypeCast::toNullableInt(null);    // null
TypeCast::toNullableInt('42');    // 42
TypeCast::toNullableFloat(null);  // null
TypeCast::toNullableString(null); // null
TypeCast::toNullableBool(null);   // null
```

---

## Each

Applies a validation rule to every item in an array. Fails fast on the first violation and prepends the index path to the exception message.

```php
use Tezmanian\Utils\Validate\Each;
use Tezmanian\Utils\Validate\Guard;

$orders = [
    ['amount' => 50],
    ['amount' => -10],
];

Each::validate($orders, function (mixed $order): void {
    if ($order['amount'] <= 0) {
        throw new \DomainException('Amount must be positive.');
    }
});
// throws DomainException: "items[1]: Amount must be positive."

// Custom path label
Each::validate($lines, fn($line) => Guard::notEmpty($line), path: 'lines');
// throws: "lines[2]: ..."
```

The original exception type is preserved, and the original exception is available via `getPrevious()`.

---

## Requirements

- PHP 8.3+
- No runtime dependencies

## Development

```bash
make build && make install
make ci   # audit, PHPStan, code style, tests
```

## License

MIT — see [LICENSE](LICENSE).

Author: René Halberstadt
