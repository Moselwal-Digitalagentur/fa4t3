<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class ConfigurationService
{
    /** @var ExtensionConfiguration */
    private $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function getGlobalApiKey(): string
    {
        return (string)$this->getExtensionSetting('apiKey');
    }

    public function getCacheDuration(): int
    {
        $duration = $this->getExtensionSetting('cacheDuration');
        return $duration ? (int)$duration : 300;
    }

    public function getDefaultDateRange(): string
    {
        $range = $this->getExtensionSetting('defaultDateRange');
        return $range ? (string)$range : '30d';
    }

    public function getSiteId(SiteInterface $site): string
    {
        if ($site instanceof NullSite) {
            return '';
        }
        $config = $site->getConfiguration();
        return isset($config['fathomSiteId']) ? (string)$config['fathomSiteId'] : '';
    }

    public function getApiKeyForSite(SiteInterface $site): string
    {
        if ($site instanceof NullSite) {
            return $this->getGlobalApiKey();
        }
        $config = $site->getConfiguration();
        $siteKey = isset($config['fathomApiKeyOverride']) ? (string)$config['fathomApiKeyOverride'] : '';

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
            'enabled' => !empty($config['fathomTrackingEnabled']),
            'customDomain' => isset($config['fathomCustomDomain']) ? (string)$config['fathomCustomDomain'] : '',
            'excludedPages' => isset($config['fathomExcludedPages']) ? (string)$config['fathomExcludedPages'] : '',
            'consentCategory' => isset($config['fathomConsentCategory']) ? (string)$config['fathomConsentCategory'] : '',
            'spaMode' => isset($config['fathomSpaMode']) ? (string)$config['fathomSpaMode'] : '',
            'honorDnt' => !empty($config['fathomHonorDnt']),
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

    /**
     * @return mixed
     */
    private function getExtensionSetting(string $key)
    {
        try {
            return $this->extensionConfiguration->get('fathom_analytics', $key);
        } catch (\Exception $e) {
            return null;
        }
    }
}
