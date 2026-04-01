<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Middleware;

use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\NullSite;

final readonly class TrackingScriptMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ConfigurationService $configurationService,
        private AssetCollector $assetCollector,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if ($site === null || $site instanceof NullSite) {
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

        $pageId = $routing->getPageId();

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
        } catch (\Exception) {
            // If rootline cannot be resolved, do not exclude
        }

        return false;
    }

    /**
     * Build a JavaScript snippet that dynamically creates the Fathom script tag
     * when consent for the given category is granted.
     * This avoids the double-wrapping issue with addInlineJavaScript.
     */
    private function buildConsentScript(string $scriptSrc, array $attributes, string $consentCategory): string
    {
        $jsonAttrs = json_encode($attributes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $escapedSrc = htmlspecialchars($scriptSrc, ENT_QUOTES, 'UTF-8');
        $escapedCategory = htmlspecialchars($consentCategory, ENT_QUOTES, 'UTF-8');

        // Create script element dynamically; consent tools can call
        // window.__fathomLoadTracking() when consent is granted.
        // Also check for a global consent API (cookieman, klaro patterns).
        return '(function(){' .
            'function loadFathom(){' .
                'if(document.getElementById("fathom-tracking-script"))return;' .
                'var s=document.createElement("script");' .
                's.id="fathom-tracking-script";' .
                's.src=' . json_encode($escapedSrc) . ';' .
                'var attrs=' . $jsonAttrs . ';' .
                'for(var k in attrs){if(attrs.hasOwnProperty(k))s.setAttribute(k,attrs[k]);}' .
                'document.head.appendChild(s);' .
            '}' .
            'window.__fathomConsentCategory=' . json_encode($escapedCategory) . ';' .
            'window.__fathomLoadTracking=loadFathom;' .
            'if(typeof window.CookieConsent!=="undefined"&&window.CookieConsent.hasConsent&&window.CookieConsent.hasConsent(' . json_encode($escapedCategory) . ')){loadFathom();}' .
        '})();';
    }
}
