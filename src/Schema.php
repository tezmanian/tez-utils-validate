<?php

declare(strict_types=1);

namespace Tez\Utils\Validate;

/**
 * Validates an array against a shape definition.
 * Fails fast on the first violation and reports the dot-notation path.
 *
 * Schema field syntax:
 *   'string' | 'int' | 'float' | 'bool' | 'array' | 'mixed'  — required, typed
 *   '?string' (etc.)                                           — optional (absent or null)
 *   [...] (nested array)                                       — required nested schema
 *   callable                                                   — custom rule, throws on failure
 */
final class Schema
{
    /**
     * Validates $data against $schema, throwing UnexpectedValueException on the first violation.
     * The exception message contains the dot-notation path to the failing field.
     *
     * @param array<mixed> $data
     * @param array<string, mixed> $schema
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException for unknown type strings in the schema
     */
    public static function validate(array $data, array $schema): void
    {
        self::validateNode($data, $schema, '');
    }

    /**
     * @param array<mixed> $data
     * @param array<string, mixed> $schema
     */
    private static function validateNode(array $data, array $schema, string $prefix): void
    {
        foreach ($schema as $key => $definition) {
            $path = $prefix !== '' ? "{$prefix}.{$key}" : $key;

            // Detect optional marker (only applies to string type definitions).
            $optional = false;

            if (is_string($definition) && str_starts_with($definition, '?')) {
                $optional  = true;
                $definition = substr($definition, 1);
            }

            // Missing key.
            if (!array_key_exists($key, $data)) {
                if ($optional) {
                    continue;
                }

                throw new \UnexpectedValueException("{$path}: required key is missing");
            }

            $value = $data[$key];

            // Optional and null — skip further validation.
            if ($optional && $value === null) {
                continue;
            }

            // Nested schema (array of definitions).
            if (is_array($definition)) {
                if (!is_array($value)) {
                    throw new \UnexpectedValueException(
                        sprintf('%s: expected array, got %s', $path, get_debug_type($value)),
                    );
                }

                /** @var array<string, mixed> $definition */
                self::validateNode($value, $definition, $path);

                continue;
            }

            // Callable rule — throws on failure.
            if (is_callable($definition)) {
                try {
                    $definition($value);
                } catch (\Throwable $e) {
                    throw new \UnexpectedValueException(
                        sprintf('%s: %s', $path, $e->getMessage()),
                        0,
                        $e,
                    );
                }

                continue;
            }

            // String type name.
            if (is_string($definition)) {
                self::assertType($value, $definition, $path);

                continue;
            }

            throw new \InvalidArgumentException(
                sprintf('Invalid schema definition for key "%s".', $key),
            );
        }
    }

    /** Checks that $value matches the expected type name, throws otherwise. */
    private static function assertType(mixed $value, string $type, string $path): void
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'int'    => is_int($value),
            'float'  => is_float($value),
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            'mixed'  => true,
            default  => throw new \InvalidArgumentException("Unknown schema type: '{$type}'"),
        };

        if (!$valid) {
            throw new \UnexpectedValueException(
                sprintf('%s: expected %s, got %s', $path, $type, get_debug_type($value)),
            );
        }
    }
}
