<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Widgets;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class TopPagesWidget implements WidgetInterface
{
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly AnalyticsService $analyticsService,
        private readonly ConfigurationService $configurationService,
        private readonly SiteFinder $siteFinder,
        private readonly StandaloneView $view,
    ) {}

    public function renderWidgetContent(): string
    {
        $topPages = [];
        $range = DateRange::fromPreset('30d');

        $sites = $this->siteFinder->getAllSites();
        foreach ($sites as $site) {
            if (!$this->configurationService->isConfigured($site)) {
                continue;
            }

            $siteId = $this->configurationService->getSiteId($site);
            $apiKey = $this->configurationService->getApiKeyForSite($site);
            $dashboardData = $this->analyticsService->getDashboardData($siteId, $range, $apiKey);
            $topPages = $dashboardData->getTopPages();
            break;
        }

        $this->view->setTemplatePathAndFilename(
            'EXT:fathom_analytics/Resources/Private/Templates/Widgets/TopPages.html'
        );
        $this->view->assign('topPages', $topPages);
        $this->view->assign('configuration', $this->configuration);
        return $this->view->render();
    }

    public function getOptions(): array
    {
        return [];
    }
}
