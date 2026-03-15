<?php

defined('TYPO3') or die();

// v11 backend module registration (deprecated in v12, removed in v13)
// In v12+ the module is registered via Configuration/Backend/Modules.php
if (!class_exists(\TYPO3\CMS\Backend\Module\ModuleProvider::class)) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'FathomAnalytics',
        'web',
        'fathomanalytics',
        'after:info',
        [
            \Moselwal\FathomAnalytics\Controller\Backend\DashboardController::class => 'index, testConnection, pageData',
        ],
        [
            'access' => 'user,group',
            'iconIdentifier' => 'fathom-analytics-module',
            'labels' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}
