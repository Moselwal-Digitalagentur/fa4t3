<?php

declare(strict_types=1);

use Moselwal\FathomAnalytics\Service\AnalyticsService;
use Moselwal\FathomAnalytics\Service\ConfigurationService;
use Moselwal\FathomAnalytics\Widgets\CurrentVisitorsWidget;
use Moselwal\FathomAnalytics\Widgets\TopPagesWidget;
use Moselwal\FathomAnalytics\Widgets\TopReferrersWidget;
use Moselwal\FathomAnalytics\Widgets\VisitorTrendWidget;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\Site\SiteFinder;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    // Only register dashboard widgets if typo3/cms-dashboard is installed
    if (!interface_exists(\TYPO3\CMS\Dashboard\Widgets\WidgetInterface::class)) {
        return;
    }

    $services = $containerConfigurator->services();

    // Shared widget dependencies
    $analyticsRef = new Reference(AnalyticsService::class);
    $configRef = new Reference(ConfigurationService::class);
    $siteFinderRef = new Reference(SiteFinder::class);

    // Determine view service reference based on TYPO3 version
    $viewServiceId = $containerBuilder->has('dashboard.views.widget')
        ? 'dashboard.views.widget'
        : null;

    $currentVisitors = $services->set('dashboard.widget.fathom_current_visitors')
        ->class(CurrentVisitorsWidget::class)
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

    if ($viewServiceId !== null) {
        $currentVisitors->arg('$view', new Reference($viewServiceId));
    }

    $services->set('dashboard.widget.fathom_visitor_trend')
        ->class(VisitorTrendWidget::class)
        ->arg('$analyticsService', $analyticsRef)
        ->arg('$configurationService', $configRef)
        ->arg('$siteFinder', $siteFinderRef)
        ->tag('dashboard.widget', [
            'identifier' => 'fathom-visitor-trend',
            'groupNames' => 'fathom',
            'title' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.visitorTrend.title',
            'description' => 'LLL:EXT:fathom_analytics/Resources/Private/Language/locallang.xlf:widget.visitorTrend.description',
            'iconIdentifier' => 'fathom-analytics-module',
            'height' => 'medium',
            'width' => 'medium',
        ]);

    $topPages = $services->set('dashboard.widget.fathom_top_pages')
        ->class(TopPagesWidget::class)
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

    if ($viewServiceId !== null) {
        $topPages->arg('$view', new Reference($viewServiceId));
    }

    $topReferrers = $services->set('dashboard.widget.fathom_top_referrers')
        ->class(TopReferrersWidget::class)
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

    if ($viewServiceId !== null) {
        $topReferrers->arg('$view', new Reference($viewServiceId));
    }
};
