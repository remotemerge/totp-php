<?php

declare(strict_types=1);

// Init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

use RemoteMerge\Totp\TotpException;
use RemoteMerge\Totp\TotpFactory;

header('Content-Type: application/json');

try {
    // Generate Secret Key
    $totp = TotpFactory::create();
    echo json_encode(['secret' => $totp->generateSecret()], JSON_THROW_ON_ERROR);
    exit;
} catch (TotpException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
    exit;
}
