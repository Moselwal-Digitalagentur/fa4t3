<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Tests\Unit\Service;

use Moselwal\FA4T3\Service\ConfigurationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class ConfigurationServiceTest extends TestCase
{
    #[Test]
    public function getGlobalApiKeyReturnsConfiguredKey(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturnMap([
            ['fa4t3', 'apiKey', 'test-api-key'],
        ]);

        $service = new ConfigurationService($extConfig);

        self::assertSame('test-api-key', $service->getGlobalApiKey());
    }

    #[Test]
    public function getCacheDurationReturnsDefaultWhenNotConfigured(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturn(null);

        $service = new ConfigurationService($extConfig);

        self::assertSame(300, $service->getCacheDuration());
    }

    #[Test]
    public function getApiKeyForSiteReturnsSiteOverrideWhenSet(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturnMap([
            ['fa4t3', 'apiKey', 'global-key'],
        ]);

        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfiguration')->willReturn([
            'fathomApiKeyOverride' => 'site-specific-key',
        ]);

        $service = new ConfigurationService($extConfig);

        self::assertSame('site-specific-key', $service->getApiKeyForSite($site));
    }

    #[Test]
    public function getApiKeyForSiteFallsBackToGlobalWhenNoOverride(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturnMap([
            ['fa4t3', 'apiKey', 'global-key'],
        ]);

        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfiguration')->willReturn([
            'fathomApiKeyOverride' => '',
        ]);

        $service = new ConfigurationService($extConfig);

        self::assertSame('global-key', $service->getApiKeyForSite($site));
    }

    #[Test]
    public function isConfiguredReturnsTrueWhenBothKeyAndSiteIdSet(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturnMap([
            ['fa4t3', 'apiKey', 'test-key'],
        ]);

        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfiguration')->willReturn([
            'fathomSiteId' => 'ABCDEF',
            'fathomApiKeyOverride' => '',
        ]);

        $service = new ConfigurationService($extConfig);

        self::assertTrue($service->isConfigured($site));
    }

    #[Test]
    public function isConfiguredReturnsFalseWhenSiteIdMissing(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);
        $extConfig->method('get')->willReturnMap([
            ['fa4t3', 'apiKey', 'test-key'],
        ]);

        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfiguration')->willReturn([
            'fathomSiteId' => '',
            'fathomApiKeyOverride' => '',
        ]);

        $service = new ConfigurationService($extConfig);

        self::assertFalse($service->isConfigured($site));
    }

    #[Test]
    public function getTrackingConfigReturnsCorrectDefaults(): void
    {
        $extConfig = $this->createMock(ExtensionConfiguration::class);

        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfiguration')->willReturn([]);

        $service = new ConfigurationService($extConfig);
        $config = $service->getTrackingConfig($site);

        self::assertFalse($config['enabled']);
        self::assertSame('', $config['customDomain']);
        self::assertSame('', $config['consentCategory']);
        self::assertFalse($config['honorDnt']);
    }
}
