<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Controller\Backend;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\SiteFinder;

final readonly class PageDataAjaxController
{
    public function __construct(
        private ConfigurationService $configurationService,
        private AnalyticsService $analyticsService,
        private SiteFinder $siteFinder,
    ) {}

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $pageUid = (int)($request->getQueryParams()['pageUid'] ?? 0);

        if ($pageUid === 0) {
            return new JsonResponse(['success' => false, 'error' => 'No page UID provided']);
        }

        $site = $request->getAttribute('site');
        if ($site === null || $site instanceof NullSite) {
            try {
                $site = $this->siteFinder->getSiteByPageId($pageUid);
            } catch (\Exception) {
                return new JsonResponse(['success' => false, 'error' => 'Could not resolve site']);
            }
        }

        if (!$this->configurationService->isConfigured($site)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Extension not configured',
                'debug' => [
                    'siteIdentifier' => $site->getIdentifier(),
                    'hasApiKey' => $this->configurationService->getApiKeyForSite($site) !== '',
                    'hasSiteId' => $this->configurationService->getSiteId($site) !== '',
                ],
            ]);
        }

        $siteId = $this->configurationService->getSiteId($site);
        $apiKey = $this->configurationService->getApiKeyForSite($site);

        $pathname = '/';
        try {
            $uri = $site->getRouter()->generateUri((string)$pageUid);
            $pathname = $uri->getPath();
        } catch (\Exception) {
            // fallback to root
        }

        $range = DateRange::fromPreset('30d');
        $result = $this->analyticsService->getPageAnalytics($siteId, $pathname, $range, $apiKey);

        return new JsonResponse([
            'success' => !$result->hasError(),
            'data' => [
                'pageviews' => $result->getPageviews(),
                'uniques' => $result->getUniques(),
                'avgDuration' => round($result->getAvgDuration(), 1),
                'bounceRate' => round($result->getBounceRate() * 100, 1),
            ],
            'pathname' => $pathname,
            'error' => $result->hasError() ? ($result->getErrorMessage() ?? 'API unavailable') : null,
        ]);
    }
}
