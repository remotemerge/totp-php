<?php declare(strict_types=1);

// Configure file finder
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/.github/scripts',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php');

// Initialize configuration
$config = new PhpCsFixer\Config();
$config->setUsingCache(false)->setRiskyAllowed(true);

// Configure rules and return config
return $config->setRules([
    '@PER-CS3x0' => true,
    '@PER-CS3x0:risky' => true,
    '@PHP8x1Migration' => true,
    '@PHPUnit10x0Migration:risky' => true,

    // Type Safety
    'declare_strict_types' => true,
    'fully_qualified_strict_types' => true,
    // 'final_class' => true,

    // Security & Performance
    'combine_consecutive_issets' => true,
    'strict_comparison' => true,

    // Modern Features
    'modernize_types_casting' => true,
    'use_arrow_functions' => true,

    // Code Quality & Formatting
    'function_declaration' => [
        'closure_function_spacing' => 'one',
        'closure_fn_spacing' => 'one',
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
    'no_superfluous_elseif' => true,
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'phpdoc_order' => true,
    'simplified_null_return' => true,
    'single_quote' => true,
])->setFinder($finder);
