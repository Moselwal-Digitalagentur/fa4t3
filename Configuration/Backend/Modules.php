<?php

use Moselwal\FathomAnalytics\Controller\Backend\DashboardController;

return [
    'fathomanalytics' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'path' => '/module/web/fathomanalytics',
        'labels' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'fathom-analytics-module',
        'extensionName' => 'FathomAnalytics',
        'controllerActions' => [
            DashboardController::class => ['index', 'testConnection'],
        ],
    ],
];
