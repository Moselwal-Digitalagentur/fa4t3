<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Widgets;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class TopReferrersWidget implements WidgetInterface, RequestAwareWidgetInterface
{
    private ServerRequestInterface $request;

    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly BackendViewFactory $backendViewFactory,
        private readonly AnalyticsService $analyticsService,
        private readonly ConfigurationService $configurationService,
        private readonly SiteFinder $siteFinder,
    ) {}

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function renderWidgetContent(): string
    {
        $topReferrers = [];
        $range = DateRange::fromPreset('30d');

        $sites = $this->siteFinder->getAllSites();
        foreach ($sites as $site) {
            if (!$this->configurationService->isConfigured($site)) {
                continue;
            }

            $siteId = $this->configurationService->getSiteId($site);
            $apiKey = $this->configurationService->getApiKeyForSite($site);
            $dashboardData = $this->analyticsService->getDashboardData($siteId, $range, $apiKey);
            $topReferrers = $dashboardData->getTopReferrers();
            break;
        }

        $view = $this->backendViewFactory->create($this->request);
        $view->assignMultiple([
            'topReferrers' => $topReferrers,
            'configuration' => $this->configuration,
        ]);
        return $view->render('Widget/TopReferrers');
    }

    public function getOptions(): array
    {
        return [];
    }
}
