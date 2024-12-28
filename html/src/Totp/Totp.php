<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

use Exception;
use RemoteMerge\Utils\Base32;

final class Totp extends AbstractTotp
{
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
     * @throws TotpException If the secret key is invalid.
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
     * @throws TotpException If the secret key is invalid.
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
}
