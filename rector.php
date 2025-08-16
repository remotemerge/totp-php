<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();

    // Configure scan paths
    $rectorConfig->paths([
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Configure skip patterns
    $rectorConfig->skip([
        // __DIR__ . '/app/...',
    ]);

    // Configure rule sets
    $rectorConfig->sets([
        // PHP version upgrade
        LevelSetList::UP_TO_PHP_81,

        // PHPUnit improvements
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,

        // Code quality and style
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,

        // Type safety and logic
        SetList::INSTANCEOF,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::STRICT_BOOLEANS,
        SetList::TYPE_DECLARATION,
    ]);
};
