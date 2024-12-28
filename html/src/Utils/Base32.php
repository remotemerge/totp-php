<?php

declare(strict_types=1);

namespace RemoteMerge\Utils;

use RemoteMerge\Totp\TotpException;

final class Base32
{
    /**
     * The characters used in Base32 encoding.
     */
    private const CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * The padding character used in Base32 encoding.
     */
    private const PADDING_CHAR = '=';

    /**
     * Encodes binary data to Base32.
     *
     * @param string $data The binary data to encode.
     * @return string The Base32 encoded string.
     */
    public static function encodeUpper(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $binary = '';
        $length = strlen($data);
        for ($i = 0; $i < $length; ++$i) {
            $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        $binaryLength = strlen($binary);
        for ($i = 0; $i < $binaryLength; $i += 5) {
            $chunk = substr($binary, $i, 5);
            $chunk = str_pad($chunk, 5, '0');
            $output .= self::CHARACTERS[bindec($chunk)];
        }

        // Add padding if necessary
        $padding = strlen($output) % 8;
        if ($padding !== 0) {
            $output .= str_repeat(self::PADDING_CHAR, 8 - $padding);
        }

        return $output;
    }

    /**
     * Decodes a Base32 encoded string to binary data.
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

        $data = rtrim($data, self::PADDING_CHAR);
        $binary = '';

        $length = strlen($data);
        for ($i = 0; $i < $length; ++$i) {
            $char = $data[$i];
            $position = strpos(self::CHARACTERS, $char);
            if ($position === false) {
                throw new TotpException('Invalid Base32 character: ' . $char);
            }

            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $output = '';
        $binaryLength = strlen($binary);
        for ($i = 0; $i < $binaryLength; $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }

        return $output;
    }
}
