<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

use RemoteMerge\Message\MessageStore;
use RemoteMerge\Utils\Base32;

abstract class AbstractTotp
{
    /**
     * The hash algorithm to use for HMAC.
     */
    protected string $algorithm = 'sha1';

    /**
     * The length of the TOTP code.
     */
    protected int $digits = 6;

    /**
     * The duration of a time slice in seconds.
     */
    protected int $period = 30;

    /**
     * The maximum allowed discrepancy value.
     */
    protected int $maxDiscrepancy = 10;

    /**
     * The supported hash algorithms.
     */
    protected const SUPPORTED_ALGORITHMS = ['sha1', 'sha256', 'sha512'];

    /**
     * Initializes the TOTP instance with optional configuration options.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     *        Supported options: 'max_discrepancy' (non-negative int).
     * @throws TotpException If 'max_discrepancy' is not a non-negative integer.
     */
    public function __construct(array $options = [])
    {
        if (isset($options['max_discrepancy'])) {
            if (!is_int($options['max_discrepancy']) || $options['max_discrepancy'] < 0) {
                throw new TotpException(MessageStore::get('configuration.invalid_max_discrepancy'));
            }

            $this->maxDiscrepancy = $options['max_discrepancy'];
        }
    }

    /**
     * Validates the secret key.
     *
     * @param string $secret The secret key to validate.
     * @throws TotpException If the secret key is invalid.
     */
    protected function validateSecret(string $secret): void
    {
        // Check if the secret is empty
        if ($secret === '') {
            throw new TotpException(MessageStore::get('validation.secret_empty'));
        }

        // Check length divisibility by 8 (existing validation)
        if (strlen($secret) % 8 !== 0) {
            throw new TotpException(MessageStore::get('validation.secret_length'));
        }

        // Base32 validation: A-Z, 2-7, and RFC 4648 padding
        if (!Base32::isValidUpper($secret)) {
            throw new TotpException(MessageStore::get('validation.secret_characters'));
        }

        // Warn about weak secrets without throwing
        // Computed, not decoded: callers decode immediately afterwards for the HMAC,
        // and decoding here purely to call strlen() doubled the work per operation.
        $byteLength = $this->decodedByteLength($secret);

        if ($byteLength < 20) {
            error_log(MessageStore::get('security.weak_secret_log', $byteLength));
        }
    }

    /**
     * Validates the TOTP code.
     *
     * @param string $code The TOTP code to validate.
     * @throws TotpException If the code is invalid.
     */
    protected function validateCode(string $code): void
    {
        // \z, not $: PCRE's `$` also matches before a trailing newline, which let
        // "123456\n" reach hash_equals() and fail as a mismatch rather than a format error.
        if (preg_match('/\A\d{' . $this->digits . '}\z/', $code) !== 1) {
            throw new TotpException(MessageStore::get('validation.code_format', $this->digits));
        }
    }

    /**
     * Calculates the decoded byte length of a Base32 secret without decoding it.
     *
     * Only correct for input that already passed isValidUpper(): the 5-bits-per-symbol
     * arithmetic assumes a valid alphabet and RFC 4648 padding.
     *
     * @param string $secret A secret that passed Base32 format validation.
     * @return int The number of bytes the secret decodes to.
     */
    private function decodedByteLength(string $secret): int
    {
        return intdiv(strlen(rtrim($secret, '=')) * 5, 8);
    }

    /**
     * Validates that a time slice is within the supported counter domain.
     *
     * The domain is non-negative because pack('J') is unsigned: a negative slice would
     * silently wrap to a huge counter and yield a code for a pre-epoch time instead of
     * failing. Slice 0 stays valid; it is the replay sentinel.
     *
     * @param int $timeSlice The time slice to validate.
     * @throws TotpException If the time slice is negative.
     */
    protected function validateTimeSlice(int $timeSlice): void
    {
        if ($timeSlice < 0) {
            throw new TotpException(MessageStore::get('validation.time_slice_negative'));
        }
    }

    /**
     * Gets the current time slice based on the current time and the time slice duration.
     *
     * @return int The current time slice.
     */
    protected function getCurrentTimeSlice(): int
    {
        return (int) floor(time() / $this->period);
    }

    /**
     * Packs the time slice into a binary string.
     *
     * The 'J' format is the source of this package's 64-bit requirement (declared as
     * php-64bit in composer.json); PHP does not provide it on 32-bit builds.
     *
     * @param int $timeSlice The time slice to pack.
     * @return string The packed binary string (8 bytes, big-endian unsigned 64-bit integer).
     */
    protected function packTimeSlice(int $timeSlice): string
    {
        return pack('J', $timeSlice);
    }

    /**
     * Extracts the TOTP code from the HMAC hash.
     *
     * @param string $hash The HMAC hash.
     * @param int $offset The offset to start extracting the code from.
     * @return int The extracted TOTP code.
     */
    protected function extractCodeFromHash(string $hash, int $offset): int
    {
        // Extract the hash values
        $values = (ord($hash[$offset]) & 0x7f) << 24;
        $values |= ord($hash[$offset + 1]) << 16;
        $values |= ord($hash[$offset + 2]) << 8;
        $values |= ord($hash[$offset + 3]);

        return $values % (10 ** $this->digits);
    }
}
