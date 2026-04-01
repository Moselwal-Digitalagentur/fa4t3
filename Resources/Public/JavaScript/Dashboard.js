/**
 * Fathom Analytics Dashboard - Chart rendering and date range interaction
 */
(function () {
    'use strict';

    function initTrendChart() {
        var canvas = document.getElementById('fathom-trend-chart');
        if (!canvas) {
            return;
        }

        var rawData = canvas.getAttribute('data-chart-data');
        if (!rawData) {
            return;
        }

        var data;
        try {
            data = JSON.parse(rawData);
        } catch (e) {
            return;
        }

        if (!Array.isArray(data) || data.length === 0) {
            return;
        }

        var labels = [];
        var visitorsData = [];
        var pageviewsData = [];

        for (var i = 0; i < data.length; i++) {
            var item = data[i];
            labels.push(item.timestamp || item.date || '');
            visitorsData.push(item.uniques || 0);
            pageviewsData.push(item.pageviews || 0);
        }

        // Check if Chart.js is available (bundled with TYPO3 backend)
        if (typeof Chart === 'undefined') {
            return;
        }

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Visitors',
                        data: visitorsData,
                        backgroundColor: 'rgba(139, 92, 246, 0.6)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pageviews',
                        data: pageviewsData,
                        backgroundColor: 'rgba(59, 130, 246, 0.3)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrendChart);
    } else {
        initTrendChart();
    }
})();
