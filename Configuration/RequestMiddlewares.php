<?php

return [
    'frontend' => [
        'moselwal/fa4t3/tracking-script' => [
            'target' => \Moselwal\FA4T3\Middleware\TrackingScriptMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
