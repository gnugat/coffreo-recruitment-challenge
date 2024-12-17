<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('doc')
    ->files()
        ->notName('capital.php')
        ->notName('country.php')
;

// Rules based on https://github.com/Coffreo/php-cs-fixer-config
return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP71Migration' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'class_keyword_remove' => false,
        'combine_consecutive_unsets' => true,
        'declare_strict_types' => true,
        'dir_constant' => false,
        'doctrine_annotation_braces' => [
            'syntax' => 'without_braces',
        ],
        'doctrine_annotation_indentation' => true,
        'doctrine_annotation_spaces' => true,
        'ereg_to_preg' => true,
        'function_to_constant' => true,
        'general_phpdoc_annotation_remove' => false,
        'header_comment' => false,
        'heredoc_to_nowdoc' => true,
        'is_null' => true,
        'linebreak_after_opening_tag' => true,
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'mb_str_functions' => false,
        'modernize_types_casting' => true,
        'native_function_invocation' => true,
        'no_alias_functions' => true,
        'no_blank_lines_before_namespace' => false,
        'multiline_whitespace_before_semicolons' => true,
        'no_php4_constructor' => false,
        'echo_tag_syntax' => ['format' => 'long'],
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'non_printable_character' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => false,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false,
        ],
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'error_suppression' => false,
        'simplified_null_return' => false,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setRiskyAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setUsingCache(true)
    ->setFinder($finder)
;

