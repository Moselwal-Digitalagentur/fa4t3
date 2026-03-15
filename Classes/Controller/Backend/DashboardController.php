<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Controller\Backend;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Service\FathomApiClient;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DashboardController extends ActionController
{
    /** @var AnalyticsService */
    private $analyticsService;

    /** @var ConfigurationService */
    private $configurationService;

    /** @var FathomApiClient */
    private $apiClient;

    /** @var SiteFinder */
    private $siteFinder;

    public function __construct(
        AnalyticsService $analyticsService,
        ConfigurationService $configurationService,
        FathomApiClient $apiClient,
        SiteFinder $siteFinder
    ) {
        $this->analyticsService = $analyticsService;
        $this->configurationService = $configurationService;
        $this->apiClient = $apiClient;
        $this->siteFinder = $siteFinder;
    }

    public function indexAction(): ResponseInterface
    {
        $site = $this->resolveCurrentSite();

        if ($site === null || !$this->configurationService->hasGlobalApiKey()) {
            $this->view->assign('showSetup', true);
            $this->view->assign('hasApiKey', $this->configurationService->hasGlobalApiKey());
            return $this->htmlResponse();
        }

        $siteId = $this->configurationService->getSiteId($site);
        if ($siteId === '') {
            $this->view->assign('showSetup', true);
            $this->view->assign('hasApiKey', true);
            $this->view->assign('noSiteId', true);
            return $this->htmlResponse();
        }

        $apiKey = $this->configurationService->getApiKeyForSite($site);
        $dateRange = $this->resolveDateRange();

        $dashboardData = $this->analyticsService->getDashboardData($siteId, $dateRange, $apiKey);
        $eventData = $this->analyticsService->getEventOverview($siteId, $dateRange, $apiKey);

        $this->view->assignMultiple([
            'showSetup' => false,
            'dashboardData' => $dashboardData,
            'eventData' => $eventData,
            'currentDateRange' => $dateRange->getPreset(),
            'hasError' => $dashboardData->hasError(),
            'errorMessage' => $dashboardData->getErrorMessage(),
        ]);

        return $this->htmlResponse();
    }

    public function testConnectionAction(): void
    {
        $site = $this->resolveCurrentSite();
        $apiKey = '';

        if ($site !== null) {
            $apiKey = $this->configurationService->getApiKeyForSite($site);
        }

        if ($apiKey === '') {
            $apiKey = $this->configurationService->getGlobalApiKey();
        }

        if ($apiKey === '') {
            $this->addFlashMessage(
                'No API key configured.',
                'Connection Test',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $this->redirect('index');
            return;
        }

        $result = $this->apiClient->testConnection($apiKey);

        if ($result->isSuccess()) {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                \TYPO3\CMS\Core\Messaging\FlashMessage::OK
            );
        } else {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
        }

        $this->redirect('index');
    }

    public function pageDataAction(): ResponseInterface
    {
        $pageUid = (int)($this->request->getQueryParams()['pageUid'] ?? 0);

        if ($pageUid === 0) {
            return new JsonResponse(['success' => false, 'error' => 'No page UID provided']);
        }

        $site = $this->resolveCurrentSite();
        if ($site === null || !$this->configurationService->isConfigured($site)) {
            return new JsonResponse(['success' => false, 'error' => 'Extension not configured']);
        }

        $siteId = $this->configurationService->getSiteId($site);
        $apiKey = $this->configurationService->getApiKeyForSite($site);

        // Resolve page URL path
        try {
            $router = $site->getRouter();
            $uri = $router->generateUri((string)$pageUid);
            $pathname = $uri->getPath();
        } catch (\Exception $e) {
            $pathname = '/';
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
            'cached' => true,
            'error' => $result->hasError() ? $result->getErrorMessage() : null,
        ]);
    }

    /**
     * @return \TYPO3\CMS\Core\Site\Entity\SiteInterface|null
     */
    private function resolveCurrentSite()
    {
        // Try to get site from request attribute
        $site = $this->request->getAttribute('site');
        if ($site !== null) {
            return $site;
        }

        // Fallback: get first available site
        $sites = $this->siteFinder->getAllSites();
        if (!empty($sites)) {
            return reset($sites);
        }

        return null;
    }

    private function resolveDateRange(): DateRange
    {
        $preset = $this->request->getQueryParams()['dateRange'] ?? null;

        if ($preset !== null && in_array($preset, ['today', '7d', '30d', 'month', '90d', 'year'], true)) {
            return DateRange::fromPreset($preset);
        }

        $dateFrom = $this->request->getQueryParams()['dateFrom'] ?? null;
        $dateTo = $this->request->getQueryParams()['dateTo'] ?? null;

        if ($dateFrom !== null && $dateTo !== null) {
            try {
                return DateRange::fromCustom(
                    new \DateTimeImmutable($dateFrom),
                    new \DateTimeImmutable($dateTo)
                );
            } catch (\Exception $e) {
                // Fall through to default
            }
        }

        return DateRange::fromPreset($this->configurationService->getDefaultDateRange());
    }
}
