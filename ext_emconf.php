<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Blocks Registration API',
    'description' => 'This API provides an easy and reliable way to register content blocks as standalone packages.',
    'category' => 'fe',
    'author' => 'TYPO3 Structured Content Initiative',
    'author_email' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '3.0.5',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'fluid_styled_content' => '10.4.0-11.9.99',
            'bootstrap_package' => '^12.0',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Typo3Contentblocks\\ContentblocksRegApi\\' => 'Classes'
        ]
    ],
];
