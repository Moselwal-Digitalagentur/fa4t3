<?php

return [
    'frontend' => [
        'moselwal/fathom-analytics/tracking-script' => [
            'target' => \Moselwal\FathomAnalytics\Middleware\TrackingScriptMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
