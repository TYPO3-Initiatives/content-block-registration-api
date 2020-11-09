<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Structured Content Registration API',
    'description' => '//TODO',
    'category' => 'fe',
    'author' => '',
    'author_email' => '',
    'state' => 'alpha',
    'clearCacheOnLoad' => 1,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            "Typo3Contentblocks\\ContentblocksRegApi\\" => "Classes"
        ]
    ],
];
