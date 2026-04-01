<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Widgets;

use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class CurrentVisitorsWidget implements WidgetInterface, RequestAwareWidgetInterface
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

        $view = $this->backendViewFactory->create($this->request);
        $view->assignMultiple([
            'count' => $count,
            'configuration' => $this->configuration,
        ]);
        return $view->render('Widget/CurrentVisitors');
    }

    public function getOptions(): array
    {
        return [];
    }
}
