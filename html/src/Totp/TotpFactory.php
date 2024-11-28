<?php

declare(strict_types=1);

namespace RemoteMerge\Auth\Totp;

final class TotpFactory
{
    public static function create(): TotpInterface
    {
        return new Totp();
    }
}
