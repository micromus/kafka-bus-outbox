<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src/');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@PSR12' => true,
        '@PHP82Migration' => true,
        'control_structure_continuation_position' => [
            'position' => 'next_line'
        ]
    ])
    ->setFinder($finder);
