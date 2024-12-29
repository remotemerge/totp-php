<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;

final class TotpTest extends TestCase
{
    /**
     * Test the entire TOTP workflow.
     * @covers \RemoteMerge\Totp\TotpFactory
     * @throws TotpException
     */
    public function test_totp_workflow(): void
    {
        $totp = TotpFactory::create();
        $secret = $totp->generateSecret();
        $code = $totp->getCode($secret);
        $this->assertTrue($totp->verifyCode($secret, $code));

        // Simulate a user scanning the QR code and entering the code
        $uri = $totp->generateUri($secret, 'user@example.com', 'ExampleService');
        $this->assertStringContainsString('otpauth://totp/', $uri);
    }
}
