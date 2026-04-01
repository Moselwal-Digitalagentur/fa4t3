<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Widgets;

use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class CurrentVisitorsWidget implements WidgetInterface
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
        $count = 0;
        $sites = $this->siteFinder->getAllSites();

        foreach ($sites as $site) {
            if ($this->configurationService->isConfigured($site)) {
                $siteId = $this->configurationService->getSiteId($site);
                $apiKey = $this->configurationService->getApiKeyForSite($site);
                $count += $this->analyticsService->getCurrentVisitorCount($siteId, $apiKey);
                break;
            }
        }

        $this->view->setTemplatePathAndFilename(
            'EXT:fathom_analytics/Resources/Private/Templates/Widgets/CurrentVisitors.html'
        );
        $this->view->assign('count', $count);
        $this->view->assign('configuration', $this->configuration);
        return $this->view->render();
    }

    public function getOptions(): array
    {
        return [];
    }
}
