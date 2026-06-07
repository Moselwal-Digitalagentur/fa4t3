<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Tests\Unit\Service;

use Moselwal\FA4T3\Domain\Model\AggregationResult;
use Moselwal\FA4T3\Domain\Model\CurrentVisitors;
use Moselwal\FA4T3\Domain\Model\DashboardData;
use Moselwal\FA4T3\Domain\Model\DateRange;
use Moselwal\FA4T3\Exception\Fa4t3ApiException;
use Moselwal\FA4T3\Service\AnalyticsService;
use Moselwal\FA4T3\Service\ConfigurationService;
use Moselwal\FA4T3\Service\Fa4t3ApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class AnalyticsServiceTest extends TestCase
{
    #[Test]
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

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $apiClient->expects(self::never())->method('getAggregation');

        $configService = $this->createMock(ConfigurationService::class);
        $configService->method('getCacheDuration')->willReturn(300);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getDashboardData('SITE123', DateRange::fromPreset('30d'), 'api-key');

        self::assertSame(100, $result->getAggregation()->getVisits());
        self::assertFalse($result->hasError());
    }

    #[Test]
    public function getDashboardDataReturnsErrorOnApiFailureWithoutCache(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $apiClient->method('getAggregation')->willThrowException(new Fa4t3ApiException('API down'));

        $configService = $this->createMock(ConfigurationService::class);
        $configService->method('getCacheDuration')->willReturn(300);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getDashboardData('SITE123', DateRange::fromPreset('30d'), 'api-key');

        self::assertTrue($result->hasError());
        self::assertSame('API down', $result->getErrorMessage());
    }

    #[Test]
    public function getCurrentVisitorCountReturnsCachedValue(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(42);

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $apiClient->expects(self::never())->method('getCurrentVisitors');

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);

        self::assertSame(42, $service->getCurrentVisitorCount('SITE123', 'api-key'));
    }

    #[Test]
    public function getCurrentVisitorCountReturnsZeroOnApiFailure(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $apiClient->method('getCurrentVisitors')->willThrowException(new Fa4t3ApiException('API down'));

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);

        self::assertSame(0, $service->getCurrentVisitorCount('SITE123', 'api-key'));
    }

    #[Test]
    public function getPageAnalyticsReturnsErrorResultOnFailure(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $apiClient->method('getAggregation')->willThrowException(new Fa4t3ApiException('Timeout'));

        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $result = $service->getPageAnalytics('SITE123', '/about', DateRange::fromPreset('30d'), 'api-key');

        self::assertTrue($result->hasError());
    }

    #[Test]
    public function flushCacheForSiteFlushesCorrectTag(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())
            ->method('flushByTag')
            ->with('fathom_site_SITE123');

        $apiClient = $this->createMock(Fa4t3ApiClient::class);
        $configService = $this->createMock(ConfigurationService::class);

        $service = new AnalyticsService($apiClient, $cache, $configService);
        $service->flushCacheForSite('SITE123');
    }
}
