<?php

defined('TYPO3') or die();

$GLOBALS['SiteConfiguration']['site']['columns']['fathomSiteId'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSiteId',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'placeholder' => 'ABCDEFG',
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomApiKeyOverride'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomApiKeyOverride',
    'config' => [
        'type' => 'input',
        'size' => 50,
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomTrackingEnabled'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomTrackingEnabled',
    'config' => [
        'type' => 'check',
        'default' => 0,
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomCustomDomain'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomCustomDomain',
    'config' => [
        'type' => 'input',
        'size' => 50,
        'placeholder' => 'your-custom-domain.com',
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomExcludedPages'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomExcludedPages',
    'config' => [
        'type' => 'input',
        'size' => 50,
        'placeholder' => '1,5,23',
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomConsentCategory'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomConsentCategory',
    'config' => [
        'type' => 'input',
        'size' => 30,
    ],
];

// Build select items in version-compatible format
$typo3Version = (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion();
if ($typo3Version >= 12) {
    $spaModeItems = [
        ['label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.none', 'value' => ''],
        ['label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.auto', 'value' => 'auto'],
        ['label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.history', 'value' => 'history'],
        ['label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.hash', 'value' => 'hash'],
    ];
} else {
    $spaModeItems = [
        ['LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.none', ''],
        ['LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.auto', 'auto'],
        ['LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.history', 'history'],
        ['LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode.hash', 'hash'],
    ];
}

$GLOBALS['SiteConfiguration']['site']['columns']['fathomSpaMode'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomSpaMode',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $spaModeItems,
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['fathomHonorDnt'] = [
    'label' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathomHonorDnt',
    'config' => [
        'type' => 'check',
        'default' => 0,
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = ($GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] ?? '') . ',
    --div--;LLL:EXT:fathom_analytics/Resources/Private/Language/locallang_be.xlf:siteconfig.fathom.tab,
    fathomSiteId,
    fathomApiKeyOverride,
    --palette--;;fathomTracking';

$GLOBALS['SiteConfiguration']['site']['palettes']['fathomTracking'] = [
    'label' => 'Frontend Tracking',
    'showitem' => 'fathomTrackingEnabled, fathomCustomDomain, fathomExcludedPages, fathomConsentCategory, fathomSpaMode, fathomHonorDnt',
];
