<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Widgets;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class VisitorTrendWidget implements WidgetInterface, ChartDataProviderInterface
{
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly AnalyticsService $analyticsService,
        private readonly ConfigurationService $configurationService,
        private readonly SiteFinder $siteFinder,
    ) {}

    public function renderWidgetContent(): string
    {
        return '';
    }

    public function getChartData(): array
    {
        $labels = [];
        $data = [];
        $range = DateRange::fromPreset('30d');

        $sites = $this->siteFinder->getAllSites();
        foreach ($sites as $site) {
            if (!$this->configurationService->isConfigured($site)) {
                continue;
            }

            $siteId = $this->configurationService->getSiteId($site);
            $apiKey = $this->configurationService->getApiKeyForSite($site);
            $dashboardData = $this->analyticsService->getDashboardData($siteId, $range, $apiKey);
            $groupedData = $dashboardData->getAggregation()->getGroupedData();

            if (is_array($groupedData)) {
                foreach ($groupedData as $row) {
                    $labels[] = $row['date'] ?? '';
                    $data[] = (int)($row['uniques'] ?? 0);
                }
            }
            break;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $this->configuration->getTitle(),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.6)',
                    'borderColor' => 'rgba(139, 92, 246, 1)',
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getOptions(): array
    {
        return [];
    }
}
