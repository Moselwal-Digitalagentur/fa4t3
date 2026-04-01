<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\EventListener;

use Moselwal\FathomAnalytics\Service\ConfigurationService;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\PageRenderer;

#[AsEventListener('fathom-analytics/page-layout-analytics')]
final readonly class PageLayoutAnalyticsListener
{
    public function __construct(
        private PageRenderer $pageRenderer,
        private ConfigurationService $configurationService,
    ) {}

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $site = $event->getRequest()->getAttribute('site');
        if ($site === null || !$this->configurationService->isConfigured($site)) {
            return;
        }

        $this->pageRenderer->loadJavaScriptModule(
            '@moselwal/fathom-analytics/PageAnalytics.js'
        );
    }
}
