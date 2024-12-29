<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;
use RemoteMerge\Totp\TotpInterface;

final class TotpFactoryTest extends TestCase
{
    /**
     * Test creating a default TOTP instance.
     * @covers \RemoteMerge\Totp\TotpFactory::create
     * @throws TotpException
     */
    public function test_create_default(): void
    {
        $totp = TotpFactory::create();
        $this->assertInstanceOf(TotpInterface::class, $totp);
    }

    /**
     * Test creating a configured TOTP instance.
     * @covers \RemoteMerge\Totp\TotpFactory::create
     * @throws TotpException
     */
    public function test_create_configured(): void
    {
        $totp = TotpFactory::create(['algorithm' => 'sha256', 'digits' => 8, 'period' => 60]);
        $this->assertSame('sha256', $totp->getAlgorithm());
        $this->assertSame(8, $totp->getDigits());
        $this->assertSame(60, $totp->getPeriod());
    }
}
