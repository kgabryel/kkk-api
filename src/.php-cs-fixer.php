<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('migrations')
;

return new PhpCsFixer\Config()
    ->setRules([
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
            'sort_algorithm' => 'alpha'
        ],
        'braces' => [
            'position_after_functions_and_oop_constructs' => 'next'
        ],
        'no_useless_return' => true,
        'no_useless_else' => true,
        'combine_consecutive_unsets' => true,
        'no_unneeded_control_parentheses' => true,
        'cast_spaces' => ['space' => 'none'],
        'method_chaining_indentation' => true,
        'no_blank_lines_after_phpdoc' => true,
        'blank_line_after_opening_tag' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_empty_statement' => true,
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        'no_unused_imports' => true,
        'phpdoc_align' => ['align' => 'left'],
        'yoda_style' => false,
        'global_namespace_import' => ['import_classes' => true],
        'concat_space' => ['spacing' => 'one'],
        'whitespace_after_comma_in_array' => true,
        'trim_array_spaces' => true,
        'ordered_attributes' => [
            'sort_algorithm' => 'custom',
            'order' => [
                Small::class,
                Medium::class,
                Large::class,
                CoversClass::class,
                Test::class,
                TestDox::class,
                DataProvider::class,
                DataProviderExternal::class,
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'continue',
                'break',
                'exit',
                'try',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/cache/.php-cs-fixer.cache');