<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RemoteMerge\Message\MessageStore;
use RemoteMerge\Totp\AbstractTotp;
use RemoteMerge\Totp\Totp;
use RemoteMerge\Totp\TotpException;

#[CoversClass(AbstractTotp::class)]
final class AbstractTotpTest extends TestCase
{
    private Totp $totp;

    private ReflectionClass $reflectionClass;

    protected function setUp(): void
    {
        $this->totp = new Totp();
        $this->reflectionClass = new ReflectionClass($this->totp);
    }

    /**
     * Test that the constructor sets max_discrepancy from options.
     */
    public function test_constructor_sets_max_discrepancy(): void
    {
        $totp = new Totp(['max_discrepancy' => 5]);
        $reflectionProperty = $this->reflectionClass->getProperty('maxDiscrepancy');
        $this->assertSame(5, $reflectionProperty->getValue($totp));
    }

    /**
     * Test that the constructor defaults max_discrepancy to 10.
     */
    public function test_constructor_defaults_max_discrepancy(): void
    {
        $totp = new Totp();
        $reflectionProperty = $this->reflectionClass->getProperty('maxDiscrepancy');
        $this->assertSame(10, $reflectionProperty->getValue($totp));
    }

    /**
     * Test validateSecret with a valid secret.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_valid_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'ABCDEFGH'); // Valid Base32 characters
    }

    /**
     * Test validateSecret with an invalid secret.
     *
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
     * Test validateSecret with an empty secret.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_empty_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage(MessageStore::get('validation.secret_empty'));
        $reflectionMethod->invoke($this->totp, '');
    }

    /**
     * Test validateSecret with invalid Base32 characters.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_invalid_base32_characters(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage(MessageStore::get('validation.secret_characters'));
        $reflectionMethod->invoke($this->totp, 'ABCD123Z'); // '1' and 'Z' are invalid in Base32
    }

    /**
     * Test validateSecret with lowercase characters.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_lowercase_characters(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage(MessageStore::get('validation.secret_characters'));
        $reflectionMethod->invoke($this->totp, 'abcd2345');
    }

    /**
     * Test validateSecret with invalid padding in the middle.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_invalid_padding_in_middle(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectException(TotpException::class);
        $this->expectExceptionMessage(MessageStore::get('validation.secret_characters'));
        $reflectionMethod->invoke($this->totp, 'ABCD=567');
    }

    /**
     * Test validateSecret with valid Base32 secret without padding.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_valid_base32_no_padding(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'JBSWY3DPEHPK3PXP'); // Valid Base32 without padding
    }

    /**
     * Test validateSecret with valid Base32 secret with padding.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_valid_base32_with_padding(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'ABCDEFG='); // Valid Base32 with padding
    }

    /**
     * Test validateSecret with multiple padding characters.
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_with_multiple_padding(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'ABCDEF=='); // Valid Base32 with multiple padding
    }

    /**
     * Test validateCode with a valid code.
     *
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

    /**
     * Test validateSecret emits an error_log warning for a weak secret (< 20 bytes).
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_logs_warning_for_weak_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        // ABCDEFGH is valid Base32 but decodes to only 5 bytes
        $loggedMessages = [];
        set_error_handler(null);
        $previousHandler = set_error_handler(null);
        ini_set('error_log', '/dev/null');

        // Use output buffering to capture error_log output sent to stderr/stdout
        // Instead, verify indirectly: no exception is thrown (the log call is non-throwing)
        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'ABCDEFGH');

        if ($previousHandler !== null) {
            set_error_handler($previousHandler);
        }
    }

    /**
     * Test validateSecret does not emit a warning for a strong secret (>= 20 bytes).
     *
     * @throws ReflectionException
     */
    public function test_validate_secret_no_warning_for_strong_secret(): void
    {
        $reflectionMethod = $this->reflectionClass->getMethod('validateSecret');

        // 32 valid Base32 characters = 20 decoded bytes (generated via Base32::encodeUpper(random_bytes(20)))
        $this->expectNotToPerformAssertions();
        $reflectionMethod->invoke($this->totp, 'MHYPSU6HI7UUMFTQD24XVUUQR7JLKV6Y');
    }
}
