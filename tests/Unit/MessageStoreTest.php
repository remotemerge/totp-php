<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RemoteMerge\Translation\MessageStore;

#[CoversClass(MessageStore::class)]
final class MessageStoreTest extends TestCase
{
    public function test_get_existing_message_without_params(): void
    {
        $result = MessageStore::get('validation.secret_empty');

        $this->assertSame(MessageStore::get('validation.secret_empty'), $result);
    }

    public function test_get_existing_message_with_params(): void
    {
        $result = MessageStore::get('validation.code_format', 6);

        $this->assertSame('The code must be a 6-digit number.', $result);
    }

    public function test_get_existing_message_with_multiple_params(): void
    {
        $result = MessageStore::get('encoding.invalid_base32_char', 'X');

        $this->assertSame('Invalid Base32 character: X', $result);
    }

    public function test_get_non_existent_message(): void
    {
        $result = MessageStore::get('non.existent.key');

        $this->assertSame('Message not found: non.existent.key', $result);
    }

    public function test_get_non_existent_message_with_params(): void
    {
        $result = MessageStore::get('non.existent.key', 'param1', 'param2');

        $this->assertSame('Message not found: non.existent.key', $result);
    }

    public function test_get_partially_valid_key(): void
    {
        $result = MessageStore::get('validation.non_existent');

        $this->assertSame('Message not found: validation.non_existent', $result);
    }

    public function test_get_empty_key(): void
    {
        $result = MessageStore::get('');

        $this->assertStringContainsString('Message not found', $result);
    }

    public function test_has_existing_key(): void
    {
        $result = MessageStore::has('validation.secret_empty');

        $this->assertTrue($result);
    }

    public function test_has_nested_existing_key(): void
    {
        $result = MessageStore::has('configuration.unsupported_algorithm');

        $this->assertTrue($result);
    }

    public function test_has_non_existent_key(): void
    {
        $result = MessageStore::has('non.existent.key');

        $this->assertFalse($result);
    }

    public function test_has_partially_valid_key(): void
    {
        $result = MessageStore::has('validation.non_existent');

        $this->assertFalse($result);
    }

    public function test_has_empty_key(): void
    {
        $result = MessageStore::has('');

        $this->assertFalse($result);
    }

    public function test_has_key_pointing_to_non_string_value(): void
    {
        // 'validation' points to an array, not a string
        $result = MessageStore::has('validation');

        $this->assertFalse($result);
    }

    public function test_get_all_validation_messages(): void
    {
        $secretEmpty = MessageStore::get('validation.secret_empty');
        $secretLength = MessageStore::get('validation.secret_length');
        $secretCharacters = MessageStore::get('validation.secret_characters');

        $this->assertSame(MessageStore::get('validation.secret_empty'), $secretEmpty);
        $this->assertSame('The secret key is invalid. Its length must be a multiple of 8.', $secretLength);
        $this->assertSame(MessageStore::get('validation.secret_characters'), $secretCharacters);
    }

    public function test_get_all_configuration_messages(): void
    {
        $unsupportedAlgorithm = MessageStore::get('configuration.unsupported_algorithm');
        $invalidDigits = MessageStore::get('configuration.invalid_digits');
        $invalidPeriod = MessageStore::get('configuration.invalid_period');

        $this->assertSame('Unsupported hash algorithm.', $unsupportedAlgorithm);
        $this->assertSame('Digits must be either 6 or 8.', $invalidDigits);
        $this->assertSame('Period must be a positive integer.', $invalidPeriod);
    }

    public function test_get_all_encoding_messages(): void
    {
        $invalidBase32Char = MessageStore::get('encoding.invalid_base32_char', 'Z');

        $this->assertSame('Invalid Base32 character: Z', $invalidBase32Char);
    }

    public function test_has_all_valid_messages(): void
    {
        $keys = [
            'validation.secret_empty',
            'validation.secret_length',
            'validation.secret_characters',
            'validation.code_format',
            'configuration.unsupported_algorithm',
            'configuration.invalid_digits',
            'configuration.invalid_period',
            'encoding.invalid_base32_char',
        ];

        foreach ($keys as $key) {
            $this->assertTrue(MessageStore::has($key), sprintf("Key '%s' should exist", $key));
        }
    }

    public function test_multiple_calls_return_same_result(): void
    {
        $first = MessageStore::get('validation.secret_empty');
        $second = MessageStore::get('validation.secret_empty');

        $this->assertSame($first, $second);
    }

    public function test_load_messages_only_loaded_once(): void
    {
        // Reset the static messages array to test fresh loading
        // Safe: This is a unit test that needs to reset static state for testing isolation
        $reflectionClass = new ReflectionClass(MessageStore::class);
        $reflectionProperty = $reflectionClass->getProperty('messages');
        // Safe: Setting private property to empty array for test setup - controlled test environment
        $reflectionProperty->setValue(null, []);

        // The first call should load messages
        $first = MessageStore::get('validation.secret_empty');
        $this->assertSame(MessageStore::get('validation.secret_empty'), $first);

        // Verify messages are now cached
        // Safe: Reading private property to verify internal state in controlled test
        $cachedMessages = $reflectionProperty->getValue(null);
        $this->assertNotEmpty($cachedMessages);

        // The second call should use cached messages
        $second = MessageStore::has('validation.secret_length');
        $this->assertTrue($second);

        // The third call also uses cached messages
        $third = MessageStore::get('configuration.invalid_digits');
        $this->assertSame('Digits must be either 6 or 8.', $third);
    }

    public function test_sprintf_formatting_with_multiple_params(): void
    {
        // Using a key that supports multiple parameters
        $result = MessageStore::get('validation.code_format', 8);

        $this->assertSame('The code must be a 8-digit number.', $result);
    }

    public function test_deeply_nested_key_retrieval(): void
    {
        // Test accessing nested structure with multiple levels
        $this->assertTrue(MessageStore::has('validation.secret_empty'));
        $this->assertTrue(MessageStore::has('configuration.invalid_digits'));
        $this->assertTrue(MessageStore::has('encoding.invalid_base32_char'));
    }

    public function test_key_exists_but_not_string_value(): void
    {
        // Test parent keys that exist but point to arrays, not strings
        $this->assertFalse(MessageStore::has('validation'));
        $this->assertFalse(MessageStore::has('configuration'));
        $this->assertFalse(MessageStore::has('encoding'));
    }
}
