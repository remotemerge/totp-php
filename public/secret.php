<?php

declare(strict_types=1);

// Init autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;

/*
 * DEMO ONLY — hands out a secret to any caller, enrolling nothing.
 *
 * The URI and its QR encode the raw secret, so both are the credential: in
 * production serve them over TLS to an authenticated user only, and store the
 * secret encrypted rather than returning it.
 */

header('Content-Type: application/json');

try {
    // Generate Secret Key
    $totp = TotpFactory::create();
    $secret = $totp->generateSecret();
    echo json_encode([
        'secret' => $secret,
        'uri' => $totp->generateUri($secret, 'user@example.com', 'RemoteMerge'),
    ], JSON_THROW_ON_ERROR);
    exit;
} catch (TotpException $totpException) {
    http_response_code(500);
    echo json_encode(['error' => $totpException->getMessage()], JSON_THROW_ON_ERROR);
    exit;
}
