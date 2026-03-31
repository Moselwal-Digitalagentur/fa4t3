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

class TopReferrersWidget implements WidgetInterface
{
    /** @var WidgetConfigurationInterface */
    private $configuration;

    /** @var AnalyticsService */
    private $analyticsService;

    /** @var ConfigurationService */
    private $configurationService;

    /** @var SiteFinder */
    private $siteFinder;

    /** @var StandaloneView|null */
    private $view;

    public function __construct(
        WidgetConfigurationInterface $configuration,
        AnalyticsService $analyticsService,
        ConfigurationService $configurationService,
        SiteFinder $siteFinder,
        ?StandaloneView $view = null
    ) {
        $this->configuration = $configuration;
        $this->analyticsService = $analyticsService;
        $this->configurationService = $configurationService;
        $this->siteFinder = $siteFinder;
        $this->view = $view;
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

        if ($this->view !== null) {
            $this->view->setTemplatePathAndFilename(
                'EXT:fathom_analytics/Resources/Private/Templates/Widgets/TopReferrers.html'
            );
            $this->view->assign('topReferrers', $topReferrers);
            $this->view->assign('configuration', $this->configuration);
            return $this->view->render();
        }

        return '';
    }

    public function getOptions(): array
    {
        return [];
    }
}
