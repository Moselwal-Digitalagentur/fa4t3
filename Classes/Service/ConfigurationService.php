<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final readonly class ConfigurationService
{
    public function __construct(
        private ExtensionConfiguration $extensionConfiguration,
    ) {}

    public function getGlobalApiKey(): string
    {
        return (string)$this->getExtensionSetting('apiKey');
    }

    public function getCacheDuration(): int
    {
        return (int)($this->getExtensionSetting('cacheDuration') ?? 300);
    }

    public function getDefaultDateRange(): string
    {
        return (string)($this->getExtensionSetting('defaultDateRange') ?? '30d');
    }

    public function getSiteId(SiteInterface $site): string
    {
        if ($site instanceof NullSite) {
            return '';
        }
        $config = $site->getConfiguration();
        return (string)($config['fathomSiteId'] ?? '');
    }

    public function getApiKeyForSite(SiteInterface $site): string
    {
        if ($site instanceof NullSite) {
            return $this->getGlobalApiKey();
        }
        $config = $site->getConfiguration();
        $siteKey = (string)($config['fathomApiKeyOverride'] ?? '');

        if ($siteKey !== '') {
            return $siteKey;
        }

        return $this->getGlobalApiKey();
    }

    /**
     * @return array{
     *     enabled: bool,
     *     customDomain: string,
     *     excludedPages: string,
     *     consentCategory: string,
     *     spaMode: string,
     *     honorDnt: bool
     * }
     */
    public function getTrackingConfig(SiteInterface $site): array
    {
        if ($site instanceof NullSite) {
            return [
                'enabled' => false,
                'customDomain' => '',
                'excludedPages' => '',
                'consentCategory' => '',
                'spaMode' => '',
                'honorDnt' => false,
            ];
        }
        $config = $site->getConfiguration();

        return [
            'enabled' => (bool)($config['fathomTrackingEnabled'] ?? false),
            'customDomain' => (string)($config['fathomCustomDomain'] ?? ''),
            'excludedPages' => (string)($config['fathomExcludedPages'] ?? ''),
            'consentCategory' => (string)($config['fathomConsentCategory'] ?? ''),
            'spaMode' => (string)($config['fathomSpaMode'] ?? ''),
            'honorDnt' => (bool)($config['fathomHonorDnt'] ?? false),
        ];
    }

    public function isConfigured(SiteInterface $site): bool
    {
        return $this->getApiKeyForSite($site) !== '' && $this->getSiteId($site) !== '';
    }

    public function hasGlobalApiKey(): bool
    {
        return $this->getGlobalApiKey() !== '';
    }

    private function getExtensionSetting(string $key): mixed
    {
        try {
            return $this->extensionConfiguration->get('fathom_analytics', $key);
        } catch (\Exception) {
            return null;
        }
    }
}
