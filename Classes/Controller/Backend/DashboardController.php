<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Controller\Backend;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Service\FathomApiClient;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class DashboardController extends ActionController
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
        private readonly ConfigurationService $configurationService,
        private readonly FathomApiClient $apiClient,
        private readonly SiteFinder $siteFinder,
    ) {}

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
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $result = $this->apiClient->testConnection($apiKey);

        if ($result->isSuccess()) {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                ContextualFeedbackSeverity::OK
            );
        } else {
            $this->addFlashMessage(
                $result->getMessage(),
                'Connection Test',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirect('index');
    }

    private function resolveCurrentSite(): ?Site
    {
        $site = $this->request->getAttribute('site');
        if ($site !== null) {
            return $site;
        }

        $sites = $this->siteFinder->getAllSites();
        if ($sites !== []) {
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
                } catch (\Exception) {
                    // Fall through to default
                }
            }
        }

        return DateRange::fromPreset($this->configurationService->getDefaultDateRange());
    }
}
