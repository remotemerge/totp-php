<?php

declare(strict_types=1);

// Init autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;

/*
 * DEMO ONLY — not an authentication endpoint.
 *
 * The caller supplies the secret, so it controls both sides of the comparison and
 * a match proves nothing. Real verification loads the secret server-side for an
 * already-identified user, rate limits, and uses verifyCodeOnce() with an atomic
 * compare-and-set on the stored slice.
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

try {
    $body = file_get_contents('php://input');

    if ($body === false || $body === '') {
        http_response_code(400);
        echo json_encode(['error' => 'A JSON request body is required.'], JSON_THROW_ON_ERROR);
        exit;
    }

    try {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        http_response_code(400);
        echo json_encode(['error' => 'The request body is not valid JSON.'], JSON_THROW_ON_ERROR);
        exit;
    }

    if (!is_array($data) || !is_string($data['secret'] ?? null) || !is_string($data['code'] ?? null)) {
        http_response_code(400);
        echo json_encode(['error' => 'Both "secret" and "code" must be supplied as strings.'], JSON_THROW_ON_ERROR);
        exit;
    }

    // Generate Secret Key
    $totp = TotpFactory::create();
    echo json_encode(['valid' => $totp->verifyCode($data['secret'], $data['code'], 0)], JSON_THROW_ON_ERROR);
    exit;
} catch (TotpException $totpException) {
    // Invalid caller input, not a server fault.
    http_response_code(400);
    echo json_encode(['error' => $totpException->getMessage()], JSON_THROW_ON_ERROR);
    exit;
}
