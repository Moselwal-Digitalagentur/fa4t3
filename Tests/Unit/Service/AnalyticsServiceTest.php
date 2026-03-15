<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Tests\Unit\Service;

use Moselwal\FathomAnalytics\Domain\Model\AggregationResult;
use Moselwal\FathomAnalytics\Domain\Model\CurrentVisitors;
use Moselwal\FathomAnalytics\Domain\Model\DashboardData;
use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use Moselwal\FathomAnalytics\Exception\FathomApiException;
use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Service\FathomApiClient;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class AnalyticsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function getDashboardDataReturnsCachedDataOnCacheHit(): void
    {
        $cachedData = new DashboardData(
            new AggregationResult(100, 75, 200, 45.0, 0.35, new \DateTimeImmutable(), new \DateTimeImmutable()),
            [],
            [],
            new CurrentVisitors(5)
        );

        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn($cachedData);

        $apiClient = $this->createMock(FathomApiClient::class);
        $apiClient->expects(self::never())->method('getAggregation');

        $configService = $this->createMock(ConfigurationService::class);
        $configService->method('getCacheDuration')->willReturn(300);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getDashboardData('SITE123', DateRange::fromPreset('30d'), 'api-key');

        self::assertSame(100, $result->getAggregation()->getVisits());
        self::assertFalse($result->hasError());
    }

    /**
     * @test
     */
    public function getDashboardDataReturnsErrorOnApiFailureWithoutCache(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(FathomApiClient::class);
        $apiClient->method('getAggregation')->willThrowException(new FathomApiException('API down'));

        $configService = $this->createMock(ConfigurationService::class);
        $configService->method('getCacheDuration')->willReturn(300);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getDashboardData('SITE123', DateRange::fromPreset('30d'), 'api-key');

        self::assertTrue($result->hasError());
        self::assertSame('API down', $result->getErrorMessage());
    }

    /**
     * @test
     */
    public function getCurrentVisitorCountReturnsCachedValue(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(42);

        $apiClient = $this->createMock(FathomApiClient::class);
        $apiClient->expects(self::never())->method('getCurrentVisitors');

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);

        self::assertSame(42, $service->getCurrentVisitorCount('SITE123', 'api-key'));
    }

    /**
     * @test
     */
    public function getCurrentVisitorCountReturnsZeroOnApiFailure(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(FathomApiClient::class);
        $apiClient->method('getCurrentVisitors')->willThrowException(new FathomApiException('API down'));

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);

        self::assertSame(0, $service->getCurrentVisitorCount('SITE123', 'api-key'));
    }

    /**
     * @test
     */
    public function getPageAnalyticsReturnsErrorResultOnFailure(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(FathomApiClient::class);
        $apiClient->method('getAggregation')->willThrowException(new FathomApiException('Timeout'));

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getPageAnalytics('SITE123', '/about', DateRange::fromPreset('30d'), 'api-key');

        self::assertTrue($result->hasError());
    }

    /**
     * @test
     */
    public function flushCacheForSiteFlushesCorrectTag(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())
            ->method('flushByTag')
            ->with('fathom_site_SITE123');

        $apiClient = $this->createMock(FathomApiClient::class);
        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $service->flushCacheForSite('SITE123');
    }
}
