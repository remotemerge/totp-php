<?php

declare(strict_types=1);

namespace RemoteMerge\Translation;

/**
 * Static message store for centralized message management.
 */
final class MessageStore implements MessageInterface
{
    /**
     * Cached messages array loaded from the messages file.
     *
     * @var array<string, mixed>
     */
    private static array $messages = [];

    /**
     * Default fallback message when a key is not found.
     */
    private const DEFAULT_MESSAGE = 'Message not found';

    /**
     * Retrieves a message by key with optional sprintf-style formatting.
     *
     * @param string $key The message key using dot notation (e.g., 'error.validation.required').
     * @param mixed ...$params Optional parameters for sprintf-style formatting.
     */
    public static function get(string $key, ...$params): string
    {
        self::loadMessages();

        $message = self::getNestedValue(self::$messages, $key);

        return $params === [] ? $message : sprintf($message, ...$params);
    }

    /**
     * Checks if a message key exists in the message store.
     *
     * @param string $key The message key to check using dot notation.
     */
    public static function has(string $key): bool
    {
        self::loadMessages();

        return self::keyExists(self::$messages, $key);
    }

    /**
     * Loads messages from the messages.php file if not already loaded.
     */
    private static function loadMessages(): void
    {
        if (self::$messages === []) {
            self::$messages = require __DIR__ . '/messages.php';
        }
    }

    /**
     * Retrieves a nested value from an array using dot notation.
     *
     * @param array<string, mixed> $array The array to search in.
     * @param string $key The dot-separated key path (e.g., 'level1.level2.key').
     */
    private static function getNestedValue(array $array, string $key): string
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return self::DEFAULT_MESSAGE . ': ' . $key;
            }

            $value = $value[$k];
        }

        return is_string($value) ? $value : self::DEFAULT_MESSAGE . ': ' . $key;
    }

    /**
     * Checks if a nested key exists and points to a string value.
     *
     * @param array<string, mixed> $array The array to search in.
     * @param string $key The dot-separated key path to check.
     */
    private static function keyExists(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return false;
            }

            $value = $value[$k];
        }

        return is_string($value);
    }
}
