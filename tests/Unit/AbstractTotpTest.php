<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RemoteMerge\Totp\Totp;
use RemoteMerge\Totp\TotpException;

class AbstractTotpTest extends TestCase
{
    private Totp $totp;

    private ReflectionClass $reflectionClass;

    protected function setUp(): void
    {
        $this->totp = new Totp();
        $this->reflectionClass = new ReflectionClass($this->totp);
    }

    /**
     * Test validateSecret with a valid secret.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::validateSecret
     * @throws ReflectionException
     */
    public function test_validate_secret_with_valid_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, '12345678');
    }

    /**
     * Test validateSecret with an invalid secret.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::validateSecret
     * @throws ReflectionException
     */
    public function test_validate_secret_with_invalid_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('The secret key is invalid. Its length must be a multiple of 8.');
        $reflectionMethod->invoke($this->totp, '1234567');
    }

    /**
     * Test validateCode with a valid code.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::validateCode
     * @throws ReflectionException
     */
    public function test_validate_code_with_valid_code(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateCode');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, '123456');
    }

    /**
     * Test validateCode with an invalid code (wrong length).
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::validateCode
     * @throws ReflectionException
     */
    public function test_validate_code_with_invalid_length(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateCode');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('The code must be a 6-digit number.');
        $reflectionMethod->invoke($this->totp, '12345');
    }

    /**
     * Test validateCode with an invalid code (non-numeric).
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::validateCode
     * @throws ReflectionException
     */
    public function test_validate_code_with_non_numeric_code(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateCode');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('The code must be a 6-digit number.');
        $reflectionMethod->invoke($this->totp, '123abc');
    }

    /**
     * Test getCurrentTimeSlice.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::getCurrentTimeSlice
     * @throws ReflectionException
     */
    public function test_get_current_time_slice(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('getCurrentTimeSlice');

        $timeSlice = $reflectionMethod->invoke($this->totp);
        $this->assertIsInt($timeSlice);
        $this->assertSame((int) floor(time() / 30), $timeSlice);
    }

    /**
     * Test packTimeSlice with a valid time slice.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::packTimeSlice
     * @throws ReflectionException
     */
    public function test_pack_time_slice(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('packTimeSlice');

        $packed = $reflectionMethod->invoke($this->totp, 1234567890);
        $this->assertSame(8, strlen((string) $packed));
        $this->assertEquals("\x00\x00\x00\x00\x49\x96\x02\xd2", $packed);
    }

    /**
     * Test extractCodeFromHash with a valid hash and offset.
     *
     * @covers \RemoteMerge\Totp\AbstractTotp::extractCodeFromHash
     * @throws ReflectionException
     */
    public function test_extract_code_from_hash(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('extractCodeFromHash');

        $hash = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f";
        $code = $reflectionMethod->invoke($this->totp, $hash, 1);
        $this->assertIsInt($code);
        $this->assertSame(16909060 % (10 ** 6), $code);
    }
}
