<?php

use Moselwal\FathomAnalytics\Controller\Backend\PageDataAjaxController;

return [
    'fathom_analytics_page_data' => [
        'path' => '/fathom-analytics/page-data',
        'target' => PageDataAjaxController::class . '::handleRequest',
    ],
];
