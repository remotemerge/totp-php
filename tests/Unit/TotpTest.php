<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\Totp;
use RemoteMerge\Totp\TotpException;

#[CoversClass(Totp::class)]
final class TotpTest extends TestCase
{
    /**
     * Test getting the hash algorithm.
     */
    public function test_get_algorithm(): void
    {
        $totp = new Totp();
        $this->assertSame('sha1', $totp->getAlgorithm());
    }

    /**
     * Test getting the number of digits in the TOTP code.
     */
    public function test_get_digits(): void
    {
        $totp = new Totp();
        $this->assertSame(6, $totp->getDigits());
    }

    /**
     * Test getting the time slice duration.
     */
    public function test_get_period(): void
    {
        $totp = new Totp();
        $this->assertSame(30, $totp->getPeriod());
    }

    /**
     * Test generating a secret key.
     * @throws Exception
     */
    public function test_generate_secret(): void
    {
        $totp = new Totp();
        $secret = $totp->generateSecret();
        $this->assertNotEmpty($secret);
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    /**
     * Test generating a TOTP code.
     * @throws TotpException
     */
    public function test_generate_code(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $code = $totp->getCode($secret);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    /**
     * Test RFC 6238 Appendix B vectors for SHA1, SHA256, and SHA512.
     * @throws TotpException
     */
    public function test_rfc_6238_appendix_b_vectors(): void
    {
        $secrets = [
            'sha1' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
            'sha256' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZA====',
            'sha512' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNA=',
        ];

        $vectors = [
            [59, 'sha1', '94287082'],
            [59, 'sha256', '46119246'],
            [59, 'sha512', '90693936'],
            [1111111109, 'sha1', '07081804'],
            [1111111109, 'sha256', '68084774'],
            [1111111109, 'sha512', '25091201'],
            [1111111111, 'sha1', '14050471'],
            [1111111111, 'sha256', '67062674'],
            [1111111111, 'sha512', '99943326'],
            [1234567890, 'sha1', '89005924'],
            [1234567890, 'sha256', '91819424'],
            [1234567890, 'sha512', '93441116'],
            [2000000000, 'sha1', '69279037'],
            [2000000000, 'sha256', '90698825'],
            [2000000000, 'sha512', '38618901'],
            [20000000000, 'sha1', '65353130'],
            [20000000000, 'sha256', '77737706'],
            [20000000000, 'sha512', '47863826'],
        ];

        foreach ($vectors as [$time, $algorithm, $expectedCode]) {
            $totp = new Totp();
            $totp->configure(['algorithm' => $algorithm, 'digits' => 8]);

            $this->assertSame(
                $expectedCode,
                $totp->getCode($secrets[$algorithm], intdiv($time, 30)),
                sprintf('Failed RFC 6238 vector for %s at time %d.', $algorithm, $time),
            );
        }
    }

    /**
     * Test verifying a valid TOTP code.
     * @throws TotpException
     */
    public function test_verify_valid_code(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $code = $totp->getCode($secret);
        $this->assertTrue($totp->verifyCode($secret, $code));
    }

    /**
     * Test verifying an invalid TOTP code.
     * @throws TotpException
     */
    public function test_verify_invalid_code(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $this->assertFalse($totp->verifyCode($secret, '123456'));
    }

    /**
     * Test verifying a TOTP code with discrepancy.
     * @throws TotpException
     */
    public function test_verify_code_with_discrepancy(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $code = $totp->getCode($secret, (int) (time() / 30 - 1)); // Previous time slice
        $this->assertTrue($totp->verifyCode($secret, $code));
    }

    /**
     * Test verifyCode keeps matching a future slice inside the discrepancy window.
     * @throws TotpException
     */
    public function test_verify_code_matches_future_slice_with_discrepancy(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice + 1);

        $this->assertTrue($totp->verifyCode($secret, $code, 1, $currentSlice));
    }

    /**
     * Test generating a TOTP URI.
     * @throws TotpException
     */
    public function test_generate_uri(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $uri = $totp->generateUri($secret, 'user@example.com', 'ExampleService');
        $this->assertStringContainsString('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret=' . $secret, $uri);
        $this->assertStringContainsString('issuer=ExampleService', $uri);
    }

    /**
     * Test configuring TOTP parameters.
     * @throws TotpException
     */
    public function test_configure_parameters(): void
    {
        $totp = new Totp();
        $totp->configure(['algorithm' => 'sha256', 'digits' => 8, 'period' => 60]);
        $this->assertSame('sha256', $totp->getAlgorithm());
        $this->assertSame(8, $totp->getDigits());
        $this->assertSame(60, $totp->getPeriod());
    }

    /**
     * Test configuring TOTP with an invalid algorithm.
     */
    public function test_configure_invalid_algorithm(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Unsupported hash algorithm.');
        $totp = new Totp();
        $totp->configure(['algorithm' => 'md5']);
    }

    /**
     * Test configuring TOTP with an invalid number of digits.
     */
    public function test_configure_invalid_digits(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Digits must be either 6 or 8.');
        $totp = new Totp();
        $totp->configure(['digits' => 7]);
    }

    /**
     * Test configuring TOTP with an invalid period.
     */
    public function test_configure_invalid_period(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Period must be a positive integer.');
        $totp = new Totp();
        $totp->configure(['period' => -1]);
    }

    /**
     * Test verifyCode throws on negative discrepancy.
     */
    public function test_verify_code_throws_on_negative_discrepancy(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Discrepancy must be between 0 and 10.');
        $totp = new Totp();
        $totp->verifyCode('JBSWY3DPEHPK3PXP', '123456', -1);
    }

    /**
     * Test verifyCode throws when discrepancy exceeds max_discrepancy.
     */
    public function test_verify_code_throws_on_discrepancy_exceeding_max(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Discrepancy must be between 0 and 10.');
        $totp = new Totp();
        $totp->verifyCode('JBSWY3DPEHPK3PXP', '123456', 11);
    }

    /**
     * Test that the max_discrepancy constructor option is respected by verifyCode.
     */
    public function test_verify_code_respects_custom_max_discrepancy(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Discrepancy must be between 0 and 3.');
        $totp = new Totp(['max_discrepancy' => 3]);
        $totp->verifyCode('JBSWY3DPEHPK3PXP', '123456', 4);
    }

    /**
     * Test verifyCodeOnce returns a time slice when code is valid and not a replay.
     * @throws TotpException
     */
    public function test_verify_code_once_returns_slice_for_valid_code(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice);

        $result = $totp->verifyCodeOnce($secret, $code, $currentSlice - 2);

        $this->assertIsInt($result);
        $this->assertSame($currentSlice, $result);
    }

    /**
     * Test verifyCodeOnce returns null when code is invalid.
     * @throws TotpException
     */
    public function test_verify_code_once_returns_null_for_invalid_code(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);

        $result = $totp->verifyCodeOnce($secret, '000000', $currentSlice - 2);

        $this->assertNull($result);
    }

    /**
     * Test verifyCodeOnce returns null when code matches a replayed (already-accepted) slice.
     * @throws TotpException
     */
    public function test_verify_code_once_returns_null_for_replay(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice);

        // Simulate the current slice having already been accepted
        $result = $totp->verifyCodeOnce($secret, $code, $currentSlice);

        $this->assertNull($result);
    }

    /**
     * Test verifyCodeOnce still returns the future matched slice after replay filtering.
     * @throws TotpException
     */
    public function test_verify_code_once_returns_future_slice_after_replay_filter(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice + 1);

        $result = $totp->verifyCodeOnce($secret, $code, $currentSlice);

        $this->assertSame($currentSlice + 1, $result);
    }

    /**
     * Test verifyCodeOnce throws on invalid discrepancy.
     */
    public function test_verify_code_once_throws_on_invalid_discrepancy(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Discrepancy must be between 0 and 10.');
        $totp = new Totp();
        $totp->verifyCodeOnce('JBSWY3DPEHPK3PXP', '123456', 0, 11);
    }

    /**
     * Test auditSecret returns a strong result for a 20-byte secret.
     * @throws Exception
     */
    public function test_audit_secret_strong(): void
    {
        $totp = new Totp();
        $secret = $totp->generateSecret(); // always 20 bytes

        $result = $totp->auditSecret($secret);

        $this->assertSame(20, $result['length_bytes']);
        $this->assertTrue($result['is_strong']);
        $this->assertSame([], $result['warnings']);
    }

    /**
     * Test auditSecret returns a warning for a weak secret.
     */
    public function test_audit_secret_weak(): void
    {
        $totp = new Totp();
        // JBSWY3DPEHPK3PXP decodes to 10 bytes
        $result = $totp->auditSecret('JBSWY3DPEHPK3PXP');

        $this->assertSame(10, $result['length_bytes']);
        $this->assertFalse($result['is_strong']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('10 bytes', $result['warnings'][0]);
    }

    /**
     * Test auditSecret returns a warning for an empty secret.
     */
    public function test_audit_secret_empty(): void
    {
        $totp = new Totp();
        $result = $totp->auditSecret('');

        $this->assertSame(0, $result['length_bytes']);
        $this->assertFalse($result['is_strong']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('empty', $result['warnings'][0]);
    }

    /**
     * Test auditSecret returns a warning for an invalid Base32 secret.
     */
    public function test_audit_secret_invalid_base32(): void
    {
        $totp = new Totp();
        $result = $totp->auditSecret('NOT-VALID-BASE32!!');

        $this->assertSame(0, $result['length_bytes']);
        $this->assertFalse($result['is_strong']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('Base32', $result['warnings'][0]);
    }

    /**
     * Test configure handles uppercase algorithm names by normalizing them.
     */
    public function test_configure_normalizes_uppercase_algorithm(): void
    {
        $totp = new Totp();
        $totp->configure(['algorithm' => 'SHA256']);
        $this->assertSame('sha256', $totp->getAlgorithm());
    }

    /**
     * Test that a failed configure() call leaves every previous value intact.
     */
    public function test_configure_failure_does_not_partially_mutate(): void
    {
        $totp = new Totp();

        try {
            $totp->configure(['algorithm' => 'sha512', 'digits' => 7]);
            $this->fail('configure() should have thrown for digits=7.');
        } catch (TotpException) {
            // Expected; the instance must keep its original configuration.
        }

        $this->assertSame('sha1', $totp->getAlgorithm());
        $this->assertSame(6, $totp->getDigits());
        $this->assertSame(30, $totp->getPeriod());
    }

    /**
     * Test that a failed period leaves an earlier valid algorithm unapplied.
     */
    public function test_configure_failure_on_period_does_not_apply_algorithm(): void
    {
        $totp = new Totp();

        try {
            $totp->configure(['algorithm' => 'sha256', 'period' => 0]);
            $this->fail('configure() should have thrown for period=0.');
        } catch (TotpException) {
            // Expected; the instance must keep its original configuration.
        }

        $this->assertSame('sha1', $totp->getAlgorithm());
        $this->assertSame(30, $totp->getPeriod());
    }

    /**
     * Test configure rejects a non-string algorithm with a TotpException rather than
     * a native error or an array-to-string conversion warning.
     */
    public function test_configure_rejects_non_string_algorithm(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Unsupported hash algorithm.');
        $totp = new Totp();
        $totp->configure(['algorithm' => ['sha256']]);
    }

    /**
     * Test verifyCodeOnce rejects a code that collides with the last accepted slice.
     *
     * Slices 910737 and 910738 both produce 911617 for the RFC 4226 test key, so a
     * sequentially persisting consumer could otherwise accept the same code twice.
     *
     * @throws TotpException
     */
    public function test_verify_code_once_rejects_adjacent_slice_collision(): void
    {
        $totp = new Totp();
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        // The collision itself is a fixed property of the key and these two counters.
        $this->assertSame('911617', $totp->getCode($secret, 910737));
        $this->assertSame('911617', $totp->getCode($secret, 910738));

        // The guard that closes it: the code belonging to the last accepted slice is
        // always refused, so a collision at the next slice cannot be accepted twice.
        $currentSlice = (int) floor(time() / 30);
        $previousSlice = $currentSlice - 1;

        $this->assertNull(
            $totp->verifyCodeOnce($secret, $totp->getCode($secret, $previousSlice), $previousSlice),
        );
    }

    /**
     * Test verifyCodeOnce still accepts a genuinely new code for the current slice.
     *
     * @throws TotpException
     */
    public function test_verify_code_once_accepts_new_slice(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice);

        $this->assertSame($currentSlice, $totp->verifyCodeOnce($secret, $code, $currentSlice - 1));
    }

    /**
     * Test verifyCodeOnce treats slice 0 as the initial sentinel rather than an
     * already accepted login, so a matching current code is still accepted.
     *
     * @throws TotpException
     */
    public function test_verify_code_once_accepts_first_login_with_zero_sentinel(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP';
        $currentSlice = (int) floor(time() / 30);
        $code = $totp->getCode($secret, $currentSlice);

        $this->assertSame($currentSlice, $totp->verifyCodeOnce($secret, $code, 0));
    }
}
