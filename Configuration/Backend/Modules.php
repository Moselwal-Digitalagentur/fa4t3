<?php

declare(strict_types=1);

use Moselwal\FA4T3\Controller\Backend\DashboardController;

return [
    'fa4t3' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'path' => '/module/web/fa4t3',
        'labels' => 'LLL:EXT:fa4t3/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'fa4t3-module',
        'extensionName' => 'FA4T3',
        'controllerActions' => [
            DashboardController::class => ['index', 'testConnection'],
        ],
    ],
];
