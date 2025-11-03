<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

use Exception;
use RemoteMerge\Message\MessageStore;
use RemoteMerge\Utils\Base32;

final class Totp extends AbstractTotp implements TotpInterface
{
    /**
     * Configures the TOTP parameters.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     *        Supported options: 'algorithm' (string), 'digits' (int), 'period' (int).
     * @throws TotpException If an unsupported algorithm is provided or if options are invalid.
     */
    public function configure(array $options): void
    {
        if (isset($options['algorithm'])) {
            if (!in_array($options['algorithm'], self::SUPPORTED_ALGORITHMS, true)) {
                throw new TotpException(MessageStore::get('configuration.unsupported_algorithm'));
            }

            $this->algorithm = $options['algorithm'];
        }

        if (isset($options['digits'])) {
            if (!in_array($options['digits'], [6, 8], true)) {
                throw new TotpException(MessageStore::get('configuration.invalid_digits'));
            }

            $this->digits = $options['digits'];
        }

        if (isset($options['period'])) {
            if (!is_int($options['period']) || $options['period'] <= 0) {
                throw new TotpException(MessageStore::get('configuration.invalid_period'));
            }

            $this->period = $options['period'];
        }
    }

    /**
     * Gets the hash algorithm to use for HMAC.
     *
     * @return string The hash algorithm.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Gets the length of the TOTP code.
     *
     * @return int The length of the TOTP code.
     */
    public function getDigits(): int
    {
        return $this->digits;
    }

    /**
     * Gets the duration of a time slice in seconds.
     *
     * @return int The duration of a time slice.
     */
    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * Generates a secret key for TOTP.
     *
     * @throws Exception If an error occurs generating the secret key.
     * @return string The generated secret key in Base32 format.
     */
    public function generateSecret(): string
    {
        return Base32::encodeUpper(random_bytes(20));
    }

    /**
     * Gets the TOTP code for the given secret.
     *
     * @param string $secret The secret key in Base32 format.
     * @param int|null $timeSlice The time slice to generate the code for. Defaults to the current time slice.
     * @throws TotpException If the secret key is invalid.
     * @return string The generated TOTP code.
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        $this->validateSecret($secret);

        $timeSlice ??= $this->getCurrentTimeSlice();
        $decodedSecret = Base32::decodeUpper($secret);
        $time = $this->packTimeSlice($timeSlice);

        $hash = hash_hmac($this->algorithm, $time, $decodedSecret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

        $code = $this->extractCodeFromHash($hash, $offset);

        return str_pad((string) $code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies the TOTP code for the given secret.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $code The code to verify.
     * @param int $discrepancy The allowed discrepancy in the code. Defaults to 1.
     * @param int|null $timeSlice The time slice to verify the code for. Defaults to the current time slice.
     * @throws TotpException If the secret key is invalid.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool
    {
        $this->validateSecret($secret);
        $this->validateCode($code);

        $currentSlice = $timeSlice ?? $this->getCurrentTimeSlice();

        for ($offset = -$discrepancy; $offset <= $discrepancy; ++$offset) {
            if (hash_equals($this->getCode($secret, $currentSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a TOTP URI for QR code generation.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $label The label for the account (e.g., user@example.com).
     * @param string $issuer The issuer of the TOTP (e.g., the service name).
     * @throws TotpException If the secret key is invalid.
     * @return string The TOTP URI in the format `otpauth://totp/{issuer}:{label}?secret={secret}&issuer={issuer}&algorithm={ALGORITHM}&digits={digits}&period={period}`.
     *               The algorithm is returned in uppercase (e.g., SHA1, SHA256, SHA512) per the Key URI Format specification.
     */
    public function generateUri(string $secret, string $label, string $issuer): string
    {
        $this->validateSecret($secret);

        $strUri = 'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d';
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);

        return sprintf($strUri, $encodedIssuer, $encodedLabel, $secret, $encodedIssuer, strtoupper($this->algorithm), $this->digits, $this->period);
    }
}
