<?php

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__.'/src', __DIR__.'/tests'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP81Migration' => true,
        '@PHP82Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'single_quote' => true,
        'declare_strict_types' => false,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'yoda_style' => false,
        'increment_style' => ['style' => 'post'],
        'native_function_invocation' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => false,
        ],
    ]);
