<?php

use Moselwal\FA4T3\Controller\Backend\PageDataAjaxController;

return [
    'fa4t3_page_data' => [
        'path' => '/fa4t3/page-data',
        'target' => PageDataAjaxController::class . '::handleRequest',
    ],
];
