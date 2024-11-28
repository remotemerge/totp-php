<?php

declare(strict_types=1);

namespace RemoteMerge\Utils;

use InvalidArgumentException;

final class Base32
{
    private const CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Encodes binary data to Base32.
     *
     * @param string $data The binary data to encode.
     * @return string The Base32 encoded string.
     */
    public static function encodeUpper(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $output .= self::CHARACTERS[bindec($chunk)];
        }

        return rtrim(str_pad($output, ceil(strlen($output) / 8) * 8, '='), '=');
    }

    /**
     * Decodes a Base32 encoded string to binary data.
     *
     * @param string $data The Base32 encoded string.
     * @return string The decoded binary data.
     * @throws InvalidArgumentException If the input is not a valid Base32 string.
     */
    public static function decodeUpper(string $data): string
    {
        $data = rtrim($data, '=');
        $binary = '';

        foreach (str_split($data) as $char) {
            $position = strpos(self::CHARACTERS, $char);
            if ($position === false) {
                throw new InvalidArgumentException('Invalid Base32 string.');
            }
            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }

        return $output;
    }
}
