<?php

declare(strict_types=1);

/**
 * Message definitions for the TOTP library.
 */

return [
    /**
     * Validation error messages.
     */
    'validation' => [
        'secret_empty' => 'The secret key cannot be empty.',
        'secret_length' => 'The secret key is invalid. Its length must be a multiple of 8.',
        'secret_characters' => 'The secret key contains invalid characters.',
        'code_format' => 'The code must be a %d-digit number.',
    ],

    /**
     * Configuration error messages.
     */
    'configuration' => [
        'unsupported_algorithm' => 'Unsupported hash algorithm.',
        'invalid_digits' => 'Digits must be either 6 or 8.',
        'invalid_period' => 'Period must be a positive integer.',
    ],

    /**
     * Encoding and decoding error messages.
     */
    'encoding' => [
        'invalid_base32_char' => 'Invalid Base32 character: %s',
    ],
];
