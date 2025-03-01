<?php

declare(strict_types=1);

// Init autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;

header('Content-Type: application/json');

try {
    // Generate Secret Key
    $totp = TotpFactory::create();
    echo json_encode(['secret' => $totp->generateSecret()], JSON_THROW_ON_ERROR);
    exit;
} catch (TotpException $totpException) {
    http_response_code(500);
    echo json_encode(['error' => $totpException->getMessage()], JSON_THROW_ON_ERROR);
    exit;
}
