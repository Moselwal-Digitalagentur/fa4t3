<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\EventListener;

use Moselwal\FA4T3\Service\ConfigurationService;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\PageRenderer;

#[AsEventListener('fa4t3/page-layout-analytics')]
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
            '@moselwal/fa4t3/PageAnalytics.js'
        );
    }
}
