<?php

declare(strict_types=1);

// Runs against a registry install, so the autoloader is in the working directory;
// __DIR__ would point at this checkout instead.
require_once getcwd() . '/vendor/autoload.php';

use RemoteMerge\Totp\TotpFactory;

// RFC 6238 Appendix B, SHA1 at T=59
$totp = TotpFactory::create(['digits' => 8]);
$code = $totp->getCode('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', intdiv(59, 30));

if ($code !== '94287082') {
    fwrite(STDERR, sprintf("Smoke test failed: expected 94287082, got %s\n", $code));
    exit(1);
}

echo "Smoke test passed: RFC 6238 vector verified.\n";
