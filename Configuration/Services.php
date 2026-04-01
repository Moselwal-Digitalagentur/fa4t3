<?php

declare(strict_types=1);

use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Widgets\CurrentVisitorsWidget;
use Moselwal\FathomAnalytics\Widgets\TopPagesWidget;
use Moselwal\FathomAnalytics\Widgets\TopReferrersWidget;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Site\SiteFinder;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    if (!interface_exists(\TYPO3\CMS\Dashboard\Widgets\WidgetInterface::class)) {
        return;
    }

    $services = $containerConfigurator->services();

    $analyticsRef = new Reference(AnalyticsService::class);
    $configRef = new Reference(ConfigurationService::class);
    $siteFinderRef = new Reference(SiteFinder::class);
    $viewFactoryRef = new Reference(BackendViewFactory::class);

    $services->set('dashboard.widget.fathom_current_visitors')
        ->class(CurrentVisitorsWidget::class)
        ->arg('$backendViewFactory', $viewFactoryRef)
        ->arg('$analyticsService', $analyticsRef)
        ->arg('$configurationService', $configRef)
        ->arg('$siteFinder', $siteFinderRef)
        ->tag('dashboard.widget', [
            'identifier' => 'fathom-current-visitors',
            'groupNames' => 'fathom',
            'title' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.currentVisitors.title',
            'description' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.currentVisitors.description',
            'iconIdentifier' => 'fathom-analytics-module',
            'height' => 'small',
            'width' => 'small',
        ]);

    $services->set('dashboard.widget.fathom_top_pages')
        ->class(TopPagesWidget::class)
        ->arg('$backendViewFactory', $viewFactoryRef)
        ->arg('$analyticsService', $analyticsRef)
        ->arg('$configurationService', $configRef)
        ->arg('$siteFinder', $siteFinderRef)
        ->tag('dashboard.widget', [
            'identifier' => 'fathom-top-pages',
            'groupNames' => 'fathom',
            'title' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.topPages.title',
            'description' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.topPages.description',
            'iconIdentifier' => 'fathom-analytics-module',
            'height' => 'medium',
            'width' => 'medium',
        ]);

    $services->set('dashboard.widget.fathom_top_referrers')
        ->class(TopReferrersWidget::class)
        ->arg('$backendViewFactory', $viewFactoryRef)
        ->arg('$analyticsService', $analyticsRef)
        ->arg('$configurationService', $configRef)
        ->arg('$siteFinder', $siteFinderRef)
        ->tag('dashboard.widget', [
            'identifier' => 'fathom-top-referrers',
            'groupNames' => 'fathom',
            'title' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.topReferrers.title',
            'description' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.topReferrers.description',
            'iconIdentifier' => 'fathom-analytics-module',
            'height' => 'medium',
            'width' => 'medium',
        ]);
};
