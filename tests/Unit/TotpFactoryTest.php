<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\Totp;
use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;
use RemoteMerge\Totp\TotpInterface;

#[CoversClass(TotpFactory::class)]
final class TotpFactoryTest extends TestCase
{
    /**
     * Test creating a default TOTP instance.
     *
     * @throws TotpException
     */
    public function test_create_default(): void
    {
        $totp = TotpFactory::create();
        $this->assertInstanceOf(TotpInterface::class, $totp);
        // The factory returns the concrete class, so consumers keep the interface
        // contract while the advanced methods stay statically available.
        $this->assertInstanceOf(Totp::class, $totp);
    }

    /**
     * Test that the advanced methods documented on the factory result are callable.
     *
     * @throws TotpException
     */
    public function test_create_exposes_advanced_methods(): void
    {
        $totp = TotpFactory::create();
        $secret = $totp->generateSecret();
        $currentSlice = (int) floor(time() / 30);

        $this->assertSame(
            $currentSlice,
            $totp->verifyCodeOnce($secret, $totp->getCode($secret, $currentSlice), $currentSlice - 1),
        );
        $this->assertTrue($totp->auditSecret($secret)['is_strong']);
    }

    /**
     * Test creating a configured TOTP instance.
     *
     * @throws TotpException
     */
    public function test_create_configured(): void
    {
        $totp = TotpFactory::create(['algorithm' => 'sha256', 'digits' => 8, 'period' => 60]);
        $this->assertSame('sha256', $totp->getAlgorithm());
        $this->assertSame(8, $totp->getDigits());
        $this->assertSame(60, $totp->getPeriod());
    }

    /**
     * Test creating a TOTP instance with a custom maximum discrepancy.
     */
    public function test_create_respects_max_discrepancy(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Discrepancy must be between 0 and 3.');

        $totp = TotpFactory::create(['max_discrepancy' => 3]);
        $totp->verifyCode('JBSWY3DPEHPK3PXP', '123456', 4);
    }
}
