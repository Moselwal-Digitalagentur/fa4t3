<?php

defined('TYPO3') or die();

// Register Fathom Analytics API cache
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['fathomanalytics_api'] ??= [];

// Register PageAnalytics.js for the page module backend context
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['fathom_analytics'] =
    static function (array &$params, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer): void {
        // Only load in backend context for the page module
        if (TYPO3_MODE === 'BE' || (defined('TYPO3') && \TYPO3\CMS\Core\Http\ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'] ?? null)->isBackend())) {
            $pageRenderer->addJsFooterFile(
                'EXT:fathom_analytics/Resources/Public/JavaScript/PageAnalytics.js',
                'text/javascript',
                false,
                false,
                '',
                true
            );
        }
    };

// Register extension icon for v11
if (!class_exists(\TYPO3\CMS\Core\Configuration\Features::class) || (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 12) {
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'fathom-analytics-module',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:fathom_analytics/Resources/Public/Icons/Extension.svg']
    );
}
