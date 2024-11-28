<?php

declare(strict_types=1);

namespace RemoteMerge\Auth\Totp;

use Exception;
use RemoteMerge\Auth\Utils\Base32;

abstract class AbstractTotp implements TOTPInterface
{
    private const TIME_SLICE_DURATION = 30;
    private const CODE_LENGTH = 6;
    private const HASH_ALGORITHM = 'sha1';

    /**
     * Generates a secret key for TOTP.
     *
     * @return string The generated secret key.
     * @throws TotpException If the secret key could not be generated.
     */
    public function generateSecret(): string
    {
        try {
            return Base32::encodeUpper(random_bytes(20));
        } catch (Exception $e) {
            throw new TotpException('Failed to generate secret key.', 0, $e);
        }
    }

    /**
     * Gets the TOTP code for the given secret.
     *
     * @param string $secret The secret key.
     * @param int|null $timeSlice The time slice to generate the code for. Defaults to the current time slice.
     * @return string The generated TOTP code.
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice = $timeSlice ?? $this->getCurrentTimeSlice();
        $decodedSecret = Base32::decodeUpper($secret);
        $time = $this->packTimeSlice($timeSlice);

        $hash = hash_hmac(self::HASH_ALGORITHM, $time, $decodedSecret, true);
        $offset = ord($hash[19]) & 0x0f;

        $code = $this->extractCodeFromHash($hash, $offset);

        return str_pad((string)$code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies the TOTP code for the given secret.
     *
     * @param string $secret The secret key.
     * @param string $code The code to verify.
     * @param int $discrepancy The allowed discrepancy in the code. Defaults to 1.
     * @param int|null $timeSlice The time slice to verify the code for. Defaults to the current time slice.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool
    {
        $currentSlice = $timeSlice ?? $this->getCurrentTimeSlice();

        for ($offset = -$discrepancy; $offset <= $discrepancy; ++$offset) {
            if ($this->getCode($secret, $currentSlice + $offset) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the current time slice.
     *
     * @return int The current time slice.
     */
    private function getCurrentTimeSlice(): int
    {
        return (int)floor(time() / self::TIME_SLICE_DURATION);
    }

    /**
     * Packs the time slice into a binary string.
     *
     * @param int $timeSlice The time slice.
     * @return string The packed binary string.
     */
    private function packTimeSlice(int $timeSlice): string
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
    private function extractCodeFromHash(string $hash, int $offset): int
    {
        // Extract the hash values
        $hash1 = ord($hash[$offset]) & 0x7f;
        $hash2 = ord($hash[$offset + 1]) & 0xff;
        $hash3 = ord($hash[$offset + 2]) & 0xff;
        $hash4 = ord($hash[$offset + 3]) & 0xff;

        return (($hash1 << 24) | ($hash2 << 16) | ($hash3 << 8) | $hash4) % (10 ** self::CODE_LENGTH);
    }
}
