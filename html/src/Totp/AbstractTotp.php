<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

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
     * The supported hash algorithms.
     */
    protected const SUPPORTED_ALGORITHMS = ['sha1', 'sha256', 'sha512'];

    /**
     * Validates the secret key.
     *
     * @param string $secret The secret key to validate.
     * @throws TotpException If the secret key is invalid.
     */
    protected function validateSecret(string $secret): void
    {
        if (strlen($secret) % 8 !== 0) {
            throw new TotpException('The secret key is invalid. Its length must be a multiple of 8.');
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
        if (strlen($code) !== $this->digits || !ctype_digit($code)) {
            throw new TotpException(sprintf('The code must be a %d-digit number.', $this->digits));
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
     * @param int $timeSlice The time slice to pack.
     * @return string The packed binary string (8 bytes, big-endian).
     */
    protected function packTimeSlice(int $timeSlice): string
    {
        return str_pad(pack('N', $timeSlice), 8, "\0", STR_PAD_LEFT);
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
        $hash1 = ord($hash[$offset]) & 0x7f;
        $hash2 = ord($hash[$offset + 1]) & 0xff;
        $hash3 = ord($hash[$offset + 2]) & 0xff;
        $hash4 = ord($hash[$offset + 3]) & 0xff;

        return (($hash1 << 24) | ($hash2 << 16) | ($hash3 << 8) | $hash4) % (10 ** $this->digits);
    }
}
