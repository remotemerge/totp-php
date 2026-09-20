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
        // Staged so a later invalid option cannot leave an earlier one applied; a
        // caller that catches the exception keeps a usable instance.
        $algorithm = $this->algorithm;
        $digits = $this->digits;
        $period = $this->period;

        if (isset($options['algorithm'])) {
            if (!is_string($options['algorithm'])) {
                throw new TotpException(MessageStore::get('configuration.unsupported_algorithm'));
            }

            $algorithm = strtolower($options['algorithm']);

            if (!in_array($algorithm, self::SUPPORTED_ALGORITHMS, true)) {
                throw new TotpException(MessageStore::get('configuration.unsupported_algorithm'));
            }
        }

        if (isset($options['digits'])) {
            if (!in_array($options['digits'], [6, 8], true)) {
                throw new TotpException(MessageStore::get('configuration.invalid_digits'));
            }

            $digits = $options['digits'];
        }

        if (isset($options['period'])) {
            if (!is_int($options['period']) || $options['period'] <= 0) {
                throw new TotpException(MessageStore::get('configuration.invalid_period'));
            }

            $period = $options['period'];
        }

        $this->algorithm = $algorithm;
        $this->digits = $digits;
        $this->period = $period;
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
        $this->validateTimeSlice($timeSlice);
        $decodedSecret = Base32::decodeUpper($secret);

        return $this->getCodeFromDecodedSecret($decodedSecret, $timeSlice);
    }

    /**
     * Gets the TOTP code for an already decoded secret.
     *
     * @param string $decodedSecret The decoded binary secret key.
     * @param int $timeSlice The time slice to generate the code for.
     * @return string The generated TOTP code.
     */
    private function getCodeFromDecodedSecret(string $decodedSecret, int $timeSlice): string
    {
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
     * @throws TotpException If the secret key is invalid or discrepancy is out of range.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timeSlice = null): bool
    {
        if ($discrepancy < 0 || $discrepancy > $this->maxDiscrepancy) {
            throw new TotpException(MessageStore::get('configuration.invalid_discrepancy', $this->maxDiscrepancy));
        }

        $this->validateSecret($secret);
        $this->validateCode($code);

        $currentSlice = $timeSlice ?? $this->getCurrentTimeSlice();
        $this->validateTimeSlice($currentSlice);
        $decodedSecret = Base32::decodeUpper($secret);

        $firstSlice = max(0, $currentSlice - $discrepancy);
        $lastSlice = $currentSlice + min($discrepancy, PHP_INT_MAX - $currentSlice);

        // Counting steps keeps the candidate an int. Incrementing past PHP_INT_MAX
        // silently yields a float, which the strictly typed HMAC helper rejects.
        for ($step = 0, $steps = $lastSlice - $firstSlice; $step <= $steps; ++$step) {
            if (hash_equals($this->getCodeFromDecodedSecret($decodedSecret, $firstSlice + $step), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies the TOTP code while preventing replay attacks.
     *
     * Accepts only slices above $lastAcceptedSlice, and refuses that slice's own code
     * (adjacent slices can collide on the same digits).
     *
     * Guarantees monotonic slice progression, not a full history of spent codes: one
     * stored integer cannot express the latter. Rejecting any repeated code for its
     * whole validity window needs an expiring per-credential record in the caller.
     *
     * The returned slice must be persisted with a compare-and-set against the value
     * passed in; this class holds no state and cannot make that update atomic. Two
     * concurrent requests reading the same slice will otherwise both succeed.
     *
     * @param string $secret The secret key in Base32 format.
     * @param string $code The code to verify.
     * @param int $lastAcceptedSlice The last time slice that was successfully accepted. Use 0 on first login.
     * @param int $discrepancy The allowed discrepancy in time slices. Defaults to 1.
     * @throws TotpException If the secret key, code, discrepancy, or last accepted slice is invalid.
     * @return int|null The matched time slice if valid, or null if invalid or replay detected.
     */
    public function verifyCodeOnce(string $secret, string $code, int $lastAcceptedSlice, int $discrepancy = 1): ?int
    {
        if ($discrepancy < 0 || $discrepancy > $this->maxDiscrepancy) {
            throw new TotpException(MessageStore::get('configuration.invalid_discrepancy', $this->maxDiscrepancy));
        }

        $this->validateSecret($secret);
        $this->validateCode($code);
        $this->validateTimeSlice($lastAcceptedSlice);

        $currentSlice = $this->getCurrentTimeSlice();
        $this->validateTimeSlice($currentSlice);
        $decodedSecret = Base32::decodeUpper($secret);

        // Distinct slices can yield identical codes: with the RFC 4226 key, 910737 and
        // 910738 both produce 911617. Skipping consumed slices alone would then accept
        // that code twice, so the previous slice's code is refused outright.
        // Slice 0 is the enrollment sentinel, not a consumed login.
        if ($lastAcceptedSlice > 0
            && hash_equals($this->getCodeFromDecodedSecret($decodedSecret, $lastAcceptedSlice), $code)) {
            return null;
        }

        $firstSlice = max($lastAcceptedSlice + 1, $currentSlice - $discrepancy, 0);
        $lastSlice = $currentSlice + min($discrepancy, PHP_INT_MAX - $currentSlice);

        // Counting steps keeps the candidate an int. Incrementing past PHP_INT_MAX
        // silently yields a float, which the strictly typed HMAC helper rejects.
        for ($step = 0, $steps = $lastSlice - $firstSlice; $step <= $steps; ++$step) {
            $candidateSlice = $firstSlice + $step;

            if (hash_equals($this->getCodeFromDecodedSecret($decodedSecret, $candidateSlice), $code)) {
                return $candidateSlice;
            }
        }

        return null;
    }

    /**
     * Audits a secret key and returns diagnostic security information.
     *
     * This method never throws exceptions; all issues are reported via the
     * returned array so callers can handle them gracefully.
     *
     * `is_strong` means length >= 20 bytes and valid syntax, never entropy: 32 'A's
     * decode to 20 zero bytes and report true. Randomness is unknowable from one value.
     *
     * @param string $secret The secret key in Base32 format to audit.
     * @return array{length_bytes: int, is_strong: bool, warnings: list<string>} Diagnostic information.
     */
    public function auditSecret(string $secret): array
    {
        $warnings = [];
        $lengthBytes = 0;

        if ($secret === '') {
            $warnings[] = MessageStore::get('security.audit_secret_empty');

            return [
                'length_bytes' => 0,
                'is_strong' => false,
                'warnings' => $warnings,
            ];
        }

        // Validate a Base32 format without throwing
        if (!Base32::isValidUpper($secret)) {
            $warnings[] = MessageStore::get('security.audit_invalid_base32');

            return [
                'length_bytes' => 0,
                'is_strong' => false,
                'warnings' => $warnings,
            ];
        }

        $lengthBytes = intdiv(strlen(rtrim($secret, '=')) * 5, 8);

        if ($lengthBytes < 20) {
            $warnings[] = MessageStore::get('security.audit_weak_secret', $lengthBytes);
        }

        return [
            'length_bytes' => $lengthBytes,
            'is_strong' => $lengthBytes >= 20,
            'warnings' => $warnings,
        ];
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
     *               Trailing `=` padding is omitted from the secret, as recommended by the Key URI Format.
     */
    public function generateUri(string $secret, string $label, string $issuer): string
    {
        $this->validateSecret($secret);

        $strUri = 'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d';
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);

        return sprintf($strUri, $encodedIssuer, $encodedLabel, rtrim($secret, '='), $encodedIssuer, strtoupper($this->algorithm), $this->digits, $this->period);
    }
}
