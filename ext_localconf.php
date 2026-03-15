<?php

defined('TYPO3') or die();

// Register Fathom Analytics API cache
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['fathomanalytics_api'] ??= [];

// Register extension icon for v11 (v12+ uses Configuration/Icons.php)
$typo3MajorVersion = (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion();

if ($typo3MajorVersion < 12) {
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'fathom-analytics-module',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:fathom_analytics/Resources/Public/Icons/Extension.svg']
    );
}
