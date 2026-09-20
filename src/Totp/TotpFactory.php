<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

final class TotpFactory
{
    /**
     * Creates a new instance of the TOTP class.
     *
     * @param array<string, mixed> $options Configuration options for the TOTP instance.
     *        Supported options: 'algorithm' (string), 'digits' (int), 'period' (int), 'max_discrepancy' (int).
     * @throws TotpException If the configuration options are invalid.
     * Returns the concrete type: verifyCodeOnce() and auditSecret() are absent from
     * TotpInterface, and callers typed to the interface could not reach them.
     *
     * @return Totp A configured TOTP instance.
     */
    public static function create(array $options = []): Totp
    {
        $totp = new Totp($options);
        $totp->configure($options);

        return $totp;
    }
}
