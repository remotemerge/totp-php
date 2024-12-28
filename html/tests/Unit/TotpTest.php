<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\Totp;
use RemoteMerge\Totp\TotpException;

final class TotpTest extends TestCase
{
    /**
     * Test generating a secret key.
     * @throws TotpException
     */
    public function testGenerateSecret(): void
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
    public function testGetCode(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP'; // Example secret
        $code = $totp->getCode($secret);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    /**
     * Test verifying a valid TOTP code.
     * @throws TotpException
     */
    public function testVerifyValidCode(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP'; // Example secret
        $code = $totp->getCode($secret);
        $this->assertTrue($totp->verifyCode($secret, $code));
    }

    /**
     * Test verifying an invalid TOTP code.
     * @throws TotpException
     */
    public function testVerifyInvalidCode(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP'; // Example secret
        $this->assertFalse($totp->verifyCode($secret, '123456'));
    }

    /**
     * Test verifying a TOTP code with discrepancy.
     * @throws TotpException
     */
    public function testVerifyCodeWithDiscrepancy(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP'; // Example secret
        $code = $totp->getCode($secret, time() / 30 - 1); // Previous time slice
        $this->assertTrue($totp->verifyCode($secret, $code, 1));
    }

    /**
     * Test generating a TOTP URI.
     * @throws TotpException
     */
    public function testGenerateUri(): void
    {
        $totp = new Totp();
        $secret = 'JBSWY3DPEHPK3PXP'; // Example secret
        $uri = $totp->generateUri($secret, 'user@example.com', 'ExampleService');
        $this->assertStringContainsString('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret=' . $secret, $uri);
        $this->assertStringContainsString('issuer=ExampleService', $uri);
    }

    /**
     * Test configuring TOTP parameters.
     * @throws TotpException
     */
    public function testConfigure(): void
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
    public function testConfigureInvalidAlgorithm(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Unsupported hash algorithm.');
        $totp = new Totp();
        $totp->configure(['algorithm' => 'md5']);
    }
}
