<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('Documentation')
    ->exclude('tests/fixtures/realUrlConfigTemplate.php')
    ->exclude('tests/fixtures/realUrlConfigTemplate2.php')
    ->exclude('tests/fixtures/realUrlConfigTemplate3.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
    ])
    ->setLineEnding("\n")
;
