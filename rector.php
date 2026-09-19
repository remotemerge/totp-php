<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withParallel()

    // Configure scan paths
    ->withPaths([
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])

    // Configure skip patterns
    ->withSkip([
        // __DIR__ . '/app/...',
    ])

    // PHP version upgrade, capped at the composer.json baseline (PHP 8.1)
    ->withPhpSets()

    // PHPUnit improvements, sets picked from the installed PHPUnit version
    ->withComposerBased(phpunit: true)

    // Code quality, style, type safety and logic
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        phpunitCodeQuality: true,
    );
