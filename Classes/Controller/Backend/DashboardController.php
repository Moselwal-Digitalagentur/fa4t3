<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Controller\Backend;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Service\FathomApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

        $hasApiKey = $this->configurationService->hasGlobalApiKey()
            || ($site !== null && $this->configurationService->getApiKeyForSite($site) !== '');

        if ($site === null || !$hasApiKey) {
            $this->view->assign('showSetup', true);
            $this->view->assign('hasApiKey', $hasApiKey);
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

    public function testConnectionAction(): ResponseInterface
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
                $this->resolveFlashMessageSeverity('error')
            );
            return $this->redirect('index');
        }

        $result = $this->apiClient->testConnection($apiKey);

        if ($result->isSuccess()) {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                $this->resolveFlashMessageSeverity('ok')
            );
        } else {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                $this->resolveFlashMessageSeverity('error')
            );
        }

        return $this->redirect('index');
    }

    /**
     * AJAX endpoint for page-specific analytics data.
     * Called directly via AjaxRoutes, NOT through Extbase bootstrapping.
     */
    public static function pageDataAjaxAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageUid = (int)($request->getQueryParams()['pageUid'] ?? 0);

        if ($pageUid === 0) {
            return new JsonResponse(['success' => false, 'error' => 'No page UID provided']);
        }

        /** @var ConfigurationService $configService */
        $configService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConfigurationService::class);
        /** @var AnalyticsService $analyticsService */
        $analyticsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AnalyticsService::class);

        $site = $request->getAttribute('site');
        if ($site === null) {
            /** @var SiteFinder $siteFinder */
            $siteFinder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(SiteFinder::class);
            try {
                $site = $siteFinder->getSiteByPageId($pageUid);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => 'Could not resolve site']);
            }
        }

        if (!$configService->isConfigured($site)) {
            return new JsonResponse(['success' => false, 'error' => 'Extension not configured']);
        }

        $siteId = $configService->getSiteId($site);
        $apiKey = $configService->getApiKeyForSite($site);

        // Resolve page URL path
        $pathname = '/';
        try {
            $router = $site->getRouter();
            $uri = $router->generateUri((string)$pageUid);
            $pathname = $uri->getPath();
        } catch (\Exception $e) {
            // fallback to root
        }

        $range = DateRange::fromPreset('30d');
        $result = $analyticsService->getPageAnalytics($siteId, $pathname, $range, $apiKey);

        return new JsonResponse([
            'success' => !$result->hasError(),
            'data' => [
                'pageviews' => $result->getPageviews(),
                'uniques' => $result->getUniques(),
                'avgDuration' => round($result->getAvgDuration(), 1),
                'bounceRate' => round($result->getBounceRate() * 100, 1),
            ],
            'cached' => true,
            'error' => $result->hasError() ? 'Analytics data temporarily unavailable' : null,
        ]);
    }

    /**
     * @return \TYPO3\CMS\Core\Site\Entity\SiteInterface|null
     */
    private function resolveCurrentSite()
    {
        $site = $this->request->getAttribute('site');
        if ($site !== null) {
            return $site;
        }

        $sites = $this->siteFinder->getAllSites();
        if (!empty($sites)) {
            return reset($sites);
        }

        return null;
    }

    private function resolveDateRange(): DateRange
    {
        $params = $this->request->getQueryParams();
        $preset = $params['dateRange'] ?? null;

        if ($preset !== null && in_array($preset, ['today', '7d', '30d', 'month', '90d', 'year'], true)) {
            return DateRange::fromPreset($preset);
        }

        $dateFrom = $params['dateFrom'] ?? null;
        $dateTo = $params['dateTo'] ?? null;

        if ($dateFrom !== null && $dateTo !== null) {
            // Validate date format (Y-m-d only)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
                try {
                    $from = new \DateTimeImmutable($dateFrom);
                    $to = new \DateTimeImmutable($dateTo);
                    // Enforce max 1 year range
                    $diffDays = (int)$from->diff($to)->days;
                    if ($diffDays > 0 && $diffDays <= 366) {
                        return DateRange::fromCustom($from, $to);
                    }
                } catch (\Exception $e) {
                    // Fall through to default
                }
            }
        }

        return DateRange::fromPreset($this->configurationService->getDefaultDateRange());
    }

    /**
     * Resolve FlashMessage severity compatible with v11-14.
     * v11: FlashMessage::OK/ERROR constants
     * v12+: ContextualFeedbackSeverity enum (FlashMessage constants deprecated)
     * v13+: FlashMessage constants removed
     *
     * @param string $level 'ok', 'error', 'warning', 'info'
     * @return int|object Severity constant or enum value
     */
    private function resolveFlashMessageSeverity(string $level)
    {
        // v12+ enum
        if (class_exists(\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::class)) {
            switch ($level) {
                case 'ok':
                    return \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK;
                case 'error':
                    return \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR;
                case 'warning':
                    return \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING;
                default:
                    return \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO;
            }
        }

        // v11 constants
        switch ($level) {
            case 'ok':
                return \TYPO3\CMS\Core\Messaging\FlashMessage::OK;
            case 'error':
                return \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR;
            case 'warning':
                return \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING;
            default:
                return \TYPO3\CMS\Core\Messaging\FlashMessage::INFO;
        }
    }
}
