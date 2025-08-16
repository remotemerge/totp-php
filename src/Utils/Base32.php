<?php

declare(strict_types=1);

namespace RemoteMerge\Utils;

use RemoteMerge\Totp\TotpException;
use RemoteMerge\Translation\MessageStore;

final class Base32
{
    /**
     * Base32 character set (RFC 4648)
     */
    private const ENCODE_MAP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Pre-computed decode lookup table for O(1) character mapping.
     */
    private const DECODE_MAP = [
        'A' => 0,  'B' => 1,  'C' => 2,  'D' => 3,  'E' => 4,  'F' => 5,  'G' => 6,  'H' => 7,
        'I' => 8,  'J' => 9,  'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
        'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
        'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29, '6' => 30, '7' => 31,
    ];

    /**
     * Encodes binary data to Base32 using optimized bit manipulation.
     *
     * @param string $data The binary data to encode.
     * @return string The Base32 encoded string.
     */
    public static function encodeUpper(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $length = strlen($data);
        $output = '';
        $buffer = 0;
        $bufferLength = 0;

        // Process input byte by byte using bit manipulation
        for ($i = 0; $i < $length; $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bufferLength += 8;

            // Extract 5-bit chunks and encode them
            while ($bufferLength >= 5) {
                $bufferLength -= 5;
                $output .= self::ENCODE_MAP[($buffer >> $bufferLength) & 0x1F];
            }
        }

        // Handle remaining bits if any
        if ($bufferLength > 0) {
            $output .= self::ENCODE_MAP[($buffer << (5 - $bufferLength)) & 0x1F];
        }

        // Add RFC 4648 compliant padding
        $padLength = (8 - (strlen($output) % 8)) % 8;
        if ($padLength > 0) {
            $output .= str_repeat('=', $padLength);
        }

        return $output;
    }

    /**
     * Decodes a Base32 encoded string to binary data using optimized lookup.
     *
     * @param string $data The Base32 encoded string.
     * @throws TotpException If the input is not a valid Base32 string.
     * @return string The decoded binary data.
     */
    public static function decodeUpper(string $data): string
    {
        if ($data === '') {
            return '';
        }

        // Remove padding
        $data = rtrim($data, '=');
        $length = strlen($data);
        $output = '';
        $buffer = 0;
        $bufferLength = 0;

        // Process each character using a pre-computed lookup table
        for ($i = 0; $i < $length; $i++) {
            $char = $data[$i];

            // Check if character is valid Base32 character
            if (!isset(self::DECODE_MAP[$char])) {
                throw new TotpException(MessageStore::get('encoding.invalid_base32_char', $char));
            }

            $buffer = ($buffer << 5) | self::DECODE_MAP[$char];
            $bufferLength += 5;

            // Extract complete bytes
            if ($bufferLength >= 8) {
                $bufferLength -= 8;
                $output .= chr(($buffer >> $bufferLength) & 0xFF);
            }
        }

        return $output;
    }
}
