/**
 * Fathom Analytics - Page Module Analytics Panel
 * Loads page-specific analytics data via AJAX and displays in the page module.
 */
(function () {
    'use strict';

    function getPageUid() {
        // Try to get page UID from TYPO3 page module context
        var urlParams = new URLSearchParams(window.location.search);
        var id = urlParams.get('id');
        if (id) {
            return parseInt(id, 10);
        }
        return 0;
    }

    function createPanel() {
        var panel = document.createElement('div');
        panel.id = 'fathom-page-analytics';
        panel.className = 'callout callout-info mt-2 mb-2';
        panel.style.cssText = 'padding: 10px 15px; font-size: 0.9em;';
        panel.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading analytics...';
        return panel;
    }

    function renderData(panel, data) {
        if (!data.success) {
            panel.innerHTML = '<span class="text-muted">' + (data.error || 'Analytics unavailable') + '</span>';
            return;
        }

        var d = data.data;
        panel.innerHTML =
            '<strong>Fathom Analytics (30d):</strong> ' +
            '<span class="badge bg-primary me-2">' + d.pageviews + ' Pageviews</span>' +
            '<span class="badge bg-secondary me-2">' + d.uniques + ' Visitors</span>' +
            '<span class="badge bg-info me-2">' + d.avgDuration + 's Avg. Duration</span>' +
            '<span class="badge bg-warning">' + d.bounceRate + '% Bounce Rate</span>';
    }

    function renderError(panel) {
        panel.innerHTML = '<span class="text-muted">Analytics data temporarily unavailable.</span>';
    }

    function init() {
        var pageUid = getPageUid();
        if (pageUid === 0) {
            return;
        }

        // Find insertion point in page module
        var moduleBody = document.querySelector('.module-body') || document.querySelector('#PageLayoutController');
        if (!moduleBody) {
            return;
        }

        var panel = createPanel();
        moduleBody.insertBefore(panel, moduleBody.firstChild);

        // Determine AJAX URL based on TYPO3 version
        var ajaxUrl = TYPO3.settings.ajaxUrls && TYPO3.settings.ajaxUrls.fathom_analytics_page_data
            ? TYPO3.settings.ajaxUrls.fathom_analytics_page_data
            : '/typo3/ajax/fathom-analytics/page-data';

        var separator = ajaxUrl.indexOf('?') === -1 ? '?' : '&';
        var url = ajaxUrl + separator + 'pageUid=' + pageUid;

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) { renderData(panel, data); })
        .catch(function () { renderError(panel); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
