<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Blocks Registration API',
    'description' => 'This API provides an easy and reliable way to register content blocks as standalone packages.',
    'category' => 'fe',
    'author' => 'TYPO3 Structured Content Initiative',
    'author_email' => '',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'fluid_styled_content' => '10.4.0-0.0.0',
            'bootstrap_package' => '10.0.0-0.0.0',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            "Typo3Contentblocks\\ContentblocksRegApi\\" => "Classes"
        ]
    ],
];
