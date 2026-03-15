<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Service;

use Moselwal\FathomAnalytics\Domain\Model\AggregationRequest;
use Moselwal\FathomAnalytics\Domain\Model\AggregationResult;
use Moselwal\FathomAnalytics\Domain\Model\CurrentVisitors;
use Moselwal\FathomAnalytics\Domain\Model\DashboardData;
use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Domain\Model\EventAggregationResult;
use Moselwal\FathomAnalytics\Exception\FathomApiException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class AnalyticsService
{
    /** @var FathomApiClient */
    private $apiClient;

    /** @var FrontendInterface */
    private $cache;

    /** @var ConfigurationService */
    private $configurationService;

    public function __construct(
        FathomApiClient $apiClient,
        FrontendInterface $cache,
        ConfigurationService $configurationService
    ) {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
        $this->configurationService = $configurationService;
    }

    public function getDashboardData(string $siteId, DateRange $range, string $apiKey): DashboardData
    {
        $cacheKey = $this->buildCacheKey('dashboard', $siteId, $range);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $request = AggregationRequest::fromDateRange($range);

            $aggregation = $this->apiClient->getAggregation($siteId, $request, $apiKey);

            $topPagesRequest = $request->withFieldGrouping('pathname')
                ->withSortBy('pageviews:desc')
                ->withLimit(10);
            $topPagesData = $this->apiClient->getAggregation($siteId, $topPagesRequest, $apiKey);

            $topReferrersRequest = $request->withFieldGrouping('referrer_source')
                ->withSortBy('visits:desc')
                ->withLimit(10);
            $topReferrersData = $this->apiClient->getAggregation($siteId, $topReferrersRequest, $apiKey);

            $currentVisitors = $this->apiClient->getCurrentVisitors($siteId, $apiKey, true);

            $dashboardData = new DashboardData(
                $aggregation,
                $topPagesData->getGroupedData() ?? [],
                $topReferrersData->getGroupedData() ?? [],
                $currentVisitors
            );

            $this->cache->set(
                $cacheKey,
                $dashboardData,
                ['fathom_site_' . $siteId],
                $this->configurationService->getCacheDuration()
            );
            $this->setStaleCache($cacheKey, $dashboardData, $siteId);

            return $dashboardData;
        } catch (FathomApiException $e) {
            // Try stale cache
            $stale = $this->getStaleCache($cacheKey);
            if ($stale !== null) {
                return $stale;
            }

            return DashboardData::createError($e->getMessage());
        }
    }

    /**
     * @return EventAggregationResult[]
     */
    public function getEventOverview(string $siteId, DateRange $range, string $apiKey): array
    {
        $cacheKey = $this->buildCacheKey('events', $siteId, $range);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $events = $this->apiClient->getEvents($siteId, $apiKey);
            $request = AggregationRequest::fromDateRange($range);
            $results = [];

            foreach ($events as $event) {
                $results[] = $this->apiClient->getEventAggregation(
                    $siteId,
                    $event->getName(),
                    $request,
                    $apiKey
                );
            }

            $this->cache->set(
                $cacheKey,
                $results,
                ['fathom_site_' . $siteId],
                $this->configurationService->getCacheDuration()
            );
            $this->setStaleCache($cacheKey, $results, $siteId);

            return $results;
        } catch (FathomApiException $e) {
            $stale = $this->getStaleCache($cacheKey);
            return $stale !== null ? $stale : [];
        }
    }

    public function getPageAnalytics(string $siteId, string $pathname, DateRange $range, string $apiKey): AggregationResult
    {
        $cacheKey = $this->buildCacheKey('page', $siteId, $range, $pathname);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $request = AggregationRequest::fromDateRange($range)
                ->withFilters([['property' => 'pathname', 'operator' => 'is', 'value' => $pathname]]);

            $result = $this->apiClient->getAggregation($siteId, $request, $apiKey);

            $this->cache->set(
                $cacheKey,
                $result,
                ['fathom_site_' . $siteId],
                $this->configurationService->getCacheDuration()
            );
            $this->setStaleCache($cacheKey, $result, $siteId);

            return $result;
        } catch (FathomApiException $e) {
            $stale = $this->getStaleCache($cacheKey);
            if ($stale !== null) {
                return $stale;
            }

            return AggregationResult::createError($e->getMessage());
        }
    }

    public function getCurrentVisitorCount(string $siteId, string $apiKey): int
    {
        $cacheKey = 'fathom_current_' . md5($siteId);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return (int)$cached;
        }

        try {
            $visitors = $this->apiClient->getCurrentVisitors($siteId, $apiKey);
            $count = $visitors->getTotal();

            $this->cache->set($cacheKey, $count, ['fathom_site_' . $siteId], 60);

            return $count;
        } catch (FathomApiException $e) {
            return 0;
        }
    }

    public function flushCacheForSite(string $siteId): void
    {
        $this->cache->flushByTag('fathom_site_' . $siteId);
    }

    private function buildCacheKey(string $prefix, string $siteId, DateRange $range, string $extra = ''): string
    {
        $parts = [
            $prefix,
            $siteId,
            $range->getPreset(),
            $range->getFrom()->format('Ymd'),
            $range->getTo()->format('Ymd'),
        ];

        if ($extra !== '') {
            $parts[] = $extra;
        }

        return 'fathom_' . md5(implode('_', $parts));
    }

    /**
     * Store a stale copy with a longer TTL for graceful degradation.
     *
     * @param string $cacheKey
     * @param mixed $data
     * @param string $siteId
     */
    private function setStaleCache(string $cacheKey, $data, string $siteId): void
    {
        $staleKey = $cacheKey . '_stale';
        // Stale cache lives 10x longer than normal cache
        $staleTtl = $this->configurationService->getCacheDuration() * 10;
        $this->cache->set($staleKey, $data, ['fathom_site_' . $siteId], $staleTtl);
    }

    /**
     * @return mixed|null
     */
    private function getStaleCache(string $cacheKey)
    {
        $staleKey = $cacheKey . '_stale';
        $stale = $this->cache->get($staleKey);
        return $stale !== false ? $stale : null;
    }
}
