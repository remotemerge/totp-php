<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

abstract class AbstractTotp implements TotpInterface
{
    protected const TIME_SLICE_DURATION = 30;
    protected const CODE_LENGTH = 6;
    protected const HASH_ALGORITHM = 'sha1';

    /**
     * Gets the current time slice.
     *
     * @return int The current time slice.
     */
    protected function getCurrentTimeSlice(): int
    {
        return (int)floor(time() / self::TIME_SLICE_DURATION);
    }

    /**
     * Packs the time slice into a binary string.
     *
     * @param int $timeSlice The time slice.
     * @return string The packed binary string.
     */
    protected function packTimeSlice(int $timeSlice): string
    {
        return str_pad(pack('N', $timeSlice), 8, "\0", STR_PAD_LEFT);
    }

    /**
     * Extracts the TOTP code from the hash.
     *
     * @param string $hash The HMAC hash.
     * @param int $offset The offset to extract from.
     * @return int The extracted code.
     */
    protected function extractCodeFromHash(string $hash, int $offset): int
    {
        // Extract the hash values
        $hash1 = ord($hash[$offset]) & 0x7f;
        $hash2 = ord($hash[$offset + 1]) & 0xff;
        $hash3 = ord($hash[$offset + 2]) & 0xff;
        $hash4 = ord($hash[$offset + 3]) & 0xff;

        return (($hash1 << 24) | ($hash2 << 16) | ($hash3 << 8) | $hash4) % (10 ** self::CODE_LENGTH);
    }
}
