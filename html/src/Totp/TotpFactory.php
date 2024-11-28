<?php

declare(strict_types=1);

namespace RemoteMerge\Totp;

final class TotpFactory
{
    public static function create(): TotpInterface
    {
        return new Totp();
    }
}
