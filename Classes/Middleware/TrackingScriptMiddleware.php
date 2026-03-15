<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Middleware;

use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Page\AssetCollector;

class TrackingScriptMiddleware implements MiddlewareInterface
{
    /** @var ConfigurationService */
    private $configurationService;

    /** @var AssetCollector */
    private $assetCollector;

    public function __construct(ConfigurationService $configurationService, AssetCollector $assetCollector)
    {
        $this->configurationService = $configurationService;
        $this->assetCollector = $assetCollector;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if ($site === null) {
            return $handler->handle($request);
        }

        $trackingConfig = $this->configurationService->getTrackingConfig($site);

        if (!$trackingConfig['enabled']) {
            return $handler->handle($request);
        }

        $siteId = $this->configurationService->getSiteId($site);
        if ($siteId === '') {
            return $handler->handle($request);
        }

        // Check page exclusion
        if ($this->isPageExcluded($request, $trackingConfig['excludedPages'])) {
            return $handler->handle($request);
        }

        // Build script attributes
        $scriptDomain = $trackingConfig['customDomain'] !== ''
            ? rtrim($trackingConfig['customDomain'], '/')
            : 'https://cdn.usefathom.com';
        $scriptSrc = $scriptDomain . '/script.js';

        $attributes = [
            'data-site' => $siteId,
            'defer' => 'defer',
        ];

        if ($trackingConfig['spaMode'] !== '') {
            $attributes['data-spa'] = $trackingConfig['spaMode'];
        }

        if ($trackingConfig['honorDnt']) {
            $attributes['data-honor-dnt'] = 'true';
        }

        $consentCategory = $trackingConfig['consentCategory'];

        if ($consentCategory !== '') {
            // Consent-managed loading: render as text/plain with data-category
            // Compatible with cookieman, klaro, and similar TYPO3 consent extensions
            $this->assetCollector->addInlineJavaScript(
                'fathom-tracking-consent',
                $this->buildConsentScript($scriptSrc, $attributes, $consentCategory),
                [],
                ['priority' => false]
            );
        } else {
            // Direct loading: no consent required (Fathom is cookiefree)
            $this->assetCollector->addJavaScript(
                'fathom-tracking',
                $scriptSrc,
                $attributes,
                ['external' => true, 'priority' => false]
            );
        }

        return $handler->handle($request);
    }

    private function isPageExcluded(ServerRequestInterface $request, string $excludedPages): bool
    {
        if ($excludedPages === '') {
            return false;
        }

        $routing = $request->getAttribute('routing');
        if ($routing === null) {
            return false;
        }

        $pageId = 0;
        if (method_exists($routing, 'getPageId')) {
            $pageId = $routing->getPageId();
        } elseif (is_object($routing) && isset($routing->pageId)) {
            $pageId = (int)$routing->pageId;
        }

        if ($pageId === 0) {
            return false;
        }

        $excludedUids = array_map('intval', array_filter(explode(',', $excludedPages)));

        // Check direct UID match
        if (in_array($pageId, $excludedUids, true)) {
            return true;
        }

        // Check if page is in an excluded page tree (rootline check)
        try {
            /** @var \TYPO3\CMS\Core\Utility\RootlineUtility $rootlineUtility */
            $rootlineUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Utility\RootlineUtility::class,
                $pageId
            );
            $rootline = $rootlineUtility->get();

            foreach ($rootline as $page) {
                if (in_array((int)$page['uid'], $excludedUids, true)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // If rootline cannot be resolved, do not exclude
        }

        return false;
    }

    private function buildConsentScript(string $scriptSrc, array $attributes, string $consentCategory): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        // Output script tag with type="text/plain" and data-category for consent tools
        return '<script type="text/plain" data-category="' . htmlspecialchars($consentCategory) . '" '
            . 'src="' . htmlspecialchars($scriptSrc) . '"' . $attrString . '></script>';
    }
}
