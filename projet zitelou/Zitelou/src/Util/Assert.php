<?php

namespace App\Util;

use InvalidArgumentException;

/**
 * Minimal internal assertion helper (side-effect free).
 * All methods throw InvalidArgumentException with a clear message.
 */
final class Assert
{
	/** @param non-empty-string $message */
	public static function true(bool $value, string $message = 'Expected true'): void
	{
		if ($value !== true) {
			throw new InvalidArgumentException($message);
		}
	}

	/** @param non-empty-string $message */
	public static function false(bool $value, string $message = 'Expected false'): void
	{
		if ($value !== false) {
			throw new InvalidArgumentException($message);
		}
	}

	/** @param non-empty-string $message */
	public static function notNull(mixed $value, string $message = 'Value must not be null'): void
	{
		if ($value === null) {
			throw new InvalidArgumentException($message);
		}
	}

	/** @param non-empty-string $message */
	public static function stringNotEmpty(?string $value, string $message = 'String must be non-empty'): void
	{
		if ($value === null || $value === '') {
			throw new InvalidArgumentException($message);
		}
	}

	/** @param non-empty-string $message */
	public static function positiveInt(int $value, string $message = 'Integer must be > 0'): void
	{
		if ($value <= 0) {
			throw new InvalidArgumentException($message);
		}
	}

	/** @param non-empty-string $message */
	public static function inArray(mixed $needle, array $haystack, string $message = 'Value not allowed'): void
	{
		if (!in_array($needle, $haystack, true)) {
			throw new InvalidArgumentException($message);
		}
	}
}

