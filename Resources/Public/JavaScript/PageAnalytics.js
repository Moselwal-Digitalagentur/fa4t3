/**
 * Fathom Analytics - Page Module Analytics Panel
 * ES module for TYPO3 14 backend.
 */

class PageAnalyticsPanel {
    constructor(targetElement, pageUid) {
        this.target = targetElement;
        this.pageUid = pageUid;
        this.panel = this.createPanel();
        this.target.insertBefore(this.panel, this.target.firstChild);
        this.loadData();
    }

    createPanel() {
        const panel = document.createElement('div');
        panel.id = 'fathom-page-analytics';
        panel.className = 'callout callout-info mt-2 mb-3';
        panel.style.cssText = 'padding: 10px 15px; font-size: 0.9em;';
        panel.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading analytics...';
        return panel;
    }

    async loadData() {
        const ajaxUrl = TYPO3?.settings?.ajaxUrls?.fathom_analytics_page_data
            ?? '/typo3/ajax/fathom-analytics/page-data';
        const separator = ajaxUrl.includes('?') ? '&' : '?';
        const url = `${ajaxUrl}${separator}pageUid=${this.pageUid}`;

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            this.render(data);
        } catch {
            this.renderError();
        }
    }

    render(data) {
        this.panel.textContent = '';

        if (!data.success) {
            this.renderError(data.error);
            return;
        }

        const d = data.data;
        const label = document.createElement('strong');
        label.textContent = 'Fathom Analytics (30d): ';
        this.panel.appendChild(label);

        const badges = [
            { cls: 'bg-primary', value: d.pageviews, label: 'Pageviews' },
            { cls: 'bg-secondary', value: d.uniques, label: 'Visitors' },
            { cls: 'bg-info', value: `${d.avgDuration}s`, label: 'Avg. Duration' },
            { cls: 'bg-warning text-dark', value: `${d.bounceRate}%`, label: 'Bounce Rate' },
        ];

        for (const badge of badges) {
            const span = document.createElement('span');
            span.className = `badge ${badge.cls} me-2`;
            span.textContent = `${badge.value} ${badge.label}`;
            this.panel.appendChild(span);
        }
    }

    renderError(detail = '') {
        const msg = detail || 'Analytics data temporarily unavailable.';
        this.panel.innerHTML = `<span class="text-body-secondary">${msg}</span>`;
    }
}

// Auto-initialize when imported
const urlParams = new URLSearchParams(window.location.search);
const pageUid = parseInt(urlParams.get('id') ?? '0', 10);

if (pageUid > 0) {
    const moduleBody = document.querySelector('.module-body') ?? document.querySelector('.t3js-module-body');
    if (moduleBody) {
        new PageAnalyticsPanel(moduleBody, pageUid);
    }
}
