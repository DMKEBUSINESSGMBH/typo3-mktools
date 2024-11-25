<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('Documentation')
    ->exclude('tests/fixtures')
    ->in(__DIR__)
;

$config = new \PhpCsFixer\Config();

return $config
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'phpdoc_align' => false,
        'no_superfluous_phpdoc_tags' => false,
        'fully_qualified_strict_types' => false,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'array_destructuring',
                'arrays',
                'match',
            ],
        ],
    ])
    ->setLineEnding("\n");
