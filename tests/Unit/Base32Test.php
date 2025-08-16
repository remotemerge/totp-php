<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Totp\TotpException;
use RemoteMerge\Utils\Base32;

#[CoversClass(Base32::class)]
final class Base32Test extends TestCase
{
    /**
     * Test encoding an empty string.
     */
    public function test_encode_empty_string(): void
    {
        $this->assertSame('', Base32::encodeUpper(''));
    }

    /**
     * Test decoding an empty string.
     *
     * @throws TotpException
     */
    public function test_decode_empty_string(): void
    {
        $this->assertSame('', Base32::decodeUpper(''));
    }

    /**
     * Test encoding a simple string.
     */
    public function test_encode_simple_string(): void
    {
        $this->assertSame('JBSWY3DP', Base32::encodeUpper('Hello'));
    }

    /**
     * Test decoding a simple string.
     *
     * @throws TotpException
     */
    public function test_decode_simple_string(): void
    {
        $this->assertSame('Hello', Base32::decodeUpper('JBSWY3DP'));
    }

    /**
     * Test encoding a string with padding.
     */
    public function test_encode_with_padding(): void
    {
        $this->assertSame('JBSWY3DPEE======', Base32::encodeUpper('Hello!'));
    }

    /**
     * Test decoding a string with padding.
     *
     * @throws TotpException
     */
    public function test_decode_with_padding(): void
    {
        $this->assertSame('Hello!', Base32::decodeUpper('JBSWY3DPEE======'));
    }

    /**
     * Test decoding an invalid Base32 string.
     */
    public function test_decode_invalid_string(): void
    {
        $this->expectException(TotpException::class);
        $this->expectExceptionMessage('Invalid Base32 character: 1');
        Base32::decodeUpper('JBSWY31P');
    }
}
