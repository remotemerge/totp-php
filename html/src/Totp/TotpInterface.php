<?php

declare(strict_types=1);

namespace RemoteMerge\Auth\Totp;

interface TotpInterface
{
    /**
     * Generates a secret key for TOTP.
     *
     * @return string The generated secret key.
     */
    public function generateSecret(): string;

    /**
     * Gets the TOTP code for the given secret.
     *
     * @param string $secret The secret key.
     * @param int|null $timeSlice The time slice to generate the code for. Defaults to the current time slice.
     * @return string The generated TOTP code.
     */
    public function getCode(string $secret, ?int $timeSlice = null): string;

    /**
     * Verifies the TOTP code for the given secret.
     *
     * @param string $secret The secret key.
     * @param string $code The code to verify.
     * @param int $discrepancy The allowed discrepancy in the code. Defaults to 1.
     * @param int|null $timeSlice The time slice to verify the code for. Defaults to the current time slice.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool;
}
