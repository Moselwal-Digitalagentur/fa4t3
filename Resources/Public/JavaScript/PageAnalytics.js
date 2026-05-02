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
        panel.className = 'callout callout-info mt-2 mb-3 p-3 small';
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm me-2';
        spinner.setAttribute('role', 'status');
        panel.appendChild(spinner);
        panel.appendChild(document.createTextNode('Loading analytics...'));
        return panel;
    }

    async loadData() {
        const ajaxUrl = TYPO3?.settings?.ajaxUrls?.fa4t3_page_data
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

        const translations = data.data?.translations ?? [];
        if (translations.length === 0) {
            this.renderError('Keine Sprachen fuer diese Seite gefunden.');
            return;
        }

        const heading = document.createElement('div');
        heading.className = 'fw-semibold mb-2';
        heading.textContent = 'Fathom Analytics (30d)';
        this.panel.appendChild(heading);

        const list = document.createElement('div');
        list.className = 'd-flex flex-column gap-2';
        for (const entry of translations) {
            list.appendChild(this.renderRow(entry));
        }
        this.panel.appendChild(list);
    }

    renderRow(entry) {
        const row = document.createElement('div');
        row.className = 'd-flex flex-wrap align-items-center gap-2';

        const langTag = document.createElement('span');
        langTag.className = 'badge bg-secondary text-uppercase';
        langTag.textContent = entry.twoLetterIsoCode || `L${entry.languageId}`;
        langTag.title = entry.title || '';
        row.appendChild(langTag);

        const slug = document.createElement('code');
        slug.className = 'text-body';
        // Multi-Domain-Sites zeigen hostname mit, sodass / auf moselwal.de von / auf moselwal.com unterscheidbar ist.
        slug.textContent = entry.hostname
            ? `${entry.hostname}${entry.slug ?? ''}`
            : (entry.slug ?? '—');
        row.appendChild(slug);

        if (entry.error) {
            const errorSpan = document.createElement('span');
            errorSpan.className = 'text-body-secondary fst-italic';
            errorSpan.textContent = entry.error;
            row.appendChild(errorSpan);
            return row;
        }

        const metrics = entry.metrics ?? {};
        const badges = [
            { cls: 'bg-primary', value: metrics.pageviews ?? 0, label: 'Pageviews' },
            { cls: 'bg-secondary', value: metrics.uniques ?? 0, label: 'Visitors' },
            { cls: 'bg-info', value: `${metrics.avgDuration ?? 0}s`, label: 'Avg. Duration' },
            { cls: 'bg-warning text-dark', value: `${metrics.bounceRate ?? 0}%`, label: 'Bounce Rate' },
        ];

        for (const badge of badges) {
            const span = document.createElement('span');
            span.className = `badge ${badge.cls}`;
            span.textContent = `${badge.value} ${badge.label}`;
            row.appendChild(span);
        }

        return row;
    }

    renderError(detail = '') {
        const msg = detail || 'Analytics data temporarily unavailable.';
        this.panel.textContent = '';
        const span = document.createElement('span');
        span.className = 'text-body-secondary';
        span.textContent = msg;
        this.panel.appendChild(span);
    }
}

const urlParams = new URLSearchParams(window.location.search);
const pageUid = parseInt(urlParams.get('id') ?? '0', 10);

if (pageUid > 0) {
    const moduleBody = document.querySelector('.module-body') ?? document.querySelector('.t3js-module-body');
    if (moduleBody) {
        new PageAnalyticsPanel(moduleBody, pageUid);
    }
}
