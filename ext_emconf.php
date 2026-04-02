<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fathom Analytics',
    'description' => 'Fathom Analytics Integration for TYPO3 CMS - Backend Dashboard, Page Analytics, Frontend Tracking',
    'category' => 'plugin',
    'author' => 'Moselwal',
    'author_email' => '',
    'state' => 'beta',
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'php' => '8.5.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'secret_resolver' => '',
        ],
    ],
];
