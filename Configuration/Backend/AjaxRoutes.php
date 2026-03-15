<?php

use Moselwal\FathomAnalytics\Controller\Backend\DashboardController;

return [
    'fathom_analytics_page_data' => [
        'path' => '/fathom-analytics/page-data',
        'target' => DashboardController::class . '::pageDataAjaxAction',
    ],
];
