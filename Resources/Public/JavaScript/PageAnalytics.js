/**
 * Fathom Analytics - Page Module Analytics Panel
 * Loads page-specific analytics data via AJAX and displays in the page module.
 */
(function () {
    'use strict';

    function getPageUid() {
        var urlParams = new URLSearchParams(window.location.search);
        var id = urlParams.get('id');
        if (id) {
            return parseInt(id, 10);
        }
        return 0;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function createBadge(className, value, label) {
        var span = document.createElement('span');
        span.className = 'badge ' + className + ' me-2';
        span.textContent = value + ' ' + label;
        return span;
    }

    function createPanel() {
        var panel = document.createElement('div');
        panel.id = 'fathom-page-analytics';
        panel.className = 'callout callout-info mt-2 mb-2';
        panel.style.cssText = 'padding: 10px 15px; font-size: 0.9em;';

        var spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm';
        spinner.setAttribute('role', 'status');
        panel.appendChild(spinner);
        panel.appendChild(document.createTextNode(' Loading analytics...'));

        return panel;
    }

    function renderData(panel, data) {
        panel.textContent = '';

        if (!data.success) {
            var muted = document.createElement('span');
            muted.className = 'text-muted';
            muted.textContent = 'Analytics data temporarily unavailable.';
            panel.appendChild(muted);
            return;
        }

        var d = data.data;
        var label = document.createElement('strong');
        label.textContent = 'Fathom Analytics (30d): ';
        panel.appendChild(label);
        panel.appendChild(createBadge('bg-primary', d.pageviews, 'Pageviews'));
        panel.appendChild(createBadge('bg-secondary', d.uniques, 'Visitors'));
        panel.appendChild(createBadge('bg-info', d.avgDuration + 's', 'Avg. Duration'));
        panel.appendChild(createBadge('bg-warning', d.bounceRate + '%', 'Bounce Rate'));
    }

    function renderError(panel) {
        panel.textContent = '';
        var muted = document.createElement('span');
        muted.className = 'text-muted';
        muted.textContent = 'Analytics data temporarily unavailable.';
        panel.appendChild(muted);
    }

    function init() {
        var pageUid = getPageUid();
        if (pageUid === 0) {
            return;
        }

        var moduleBody = document.querySelector('.module-body') || document.querySelector('#PageLayoutController');
        if (!moduleBody) {
            return;
        }

        var panel = createPanel();
        moduleBody.insertBefore(panel, moduleBody.firstChild);

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
