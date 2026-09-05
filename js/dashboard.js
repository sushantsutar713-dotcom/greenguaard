/**
 * GreenGuard — Analytics Dashboard Chart.js Visualizations
 */

document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;
    
    // Global Chart.js dark theme styling
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';

    const data = window.chartData || {
        categories: { 'Illegal Dumping': 4, 'Tree Loss': 2, 'Water Pollution': 3, 'Waste Burning': 2, 'Industrial': 2, 'Plastic': 3 },
        statuses: { 'Pending': 2, 'Under Review': 1, 'Verified': 2, 'In Progress': 1, 'Resolved': 2 },
        severities: { 'Critical': 3, 'High': 4, 'Medium': 3, 'Low': 1 }
    };

    initCategoryBarChart(data.categories);
    initStatusDoughnutChart(data.statuses);
    initTrendLineChart();
    initSeverityChart(data.severities);
});

/**
 * 1. Reports by Category Horizontal/Vertical Bar Chart
 */
function initCategoryBarChart(categories) {
    const ctx = document.getElementById('categoryBarChart')?.getContext('2d');
    if (!ctx) return;

    const labels = Object.keys(categories);
    const values = Object.values(categories);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Threat Incidents',
                data: values,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.75)',
                    'rgba(14, 165, 233, 0.75)',
                    'rgba(249, 115, 22, 0.75)',
                    'rgba(239, 68, 68, 0.75)',
                    'rgba(139, 92, 246, 0.75)',
                    'rgba(234, 179, 8, 0.75)',
                    'rgba(20, 184, 166, 0.75)',
                    'rgba(168, 85, 247, 0.75)'
                ],
                borderColor: 'rgba(255, 255, 255, 0.2)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 35,
                        minRotation: 20
                    }
                }
            }
        }
    });
}

/**
 * 2. Report Status Lifecycle Doughnut Chart
 */
function initStatusDoughnutChart(statuses) {
    const ctx = document.getElementById('statusDoughnutChart')?.getContext('2d');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statuses),
            datasets: [{
                data: Object.values(statuses),
                backgroundColor: [
                    'rgba(148, 163, 184, 0.8)', // Pending
                    'rgba(234, 179, 8, 0.8)',   // Under Review
                    'rgba(139, 92, 246, 0.8)',  // Verified
                    'rgba(14, 165, 233, 0.8)',  // In Progress
                    'rgba(16, 185, 129, 0.8)'   // Resolved
                ],
                borderColor: '#0b1320',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 14,
                        padding: 14
                    }
                }
            },
            cutout: '65%'
        }
    });
}

/**
 * 3. Monthly Threat Frequency Smooth Line Chart
 */
function initTrendLineChart() {
    const ctx = document.getElementById('trendLineChart')?.getContext('2d');
    if (!ctx) return;

    // Gradient fill
    const gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep (Live)'],
            datasets: [{
                label: 'Monthly Threats Reported',
                data: [3, 7, 12, 18, 24],
                borderColor: '#10b981',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#34d399',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 5 }
                }
            }
        }
    });
}

/**
 * 4. Severity Distribution Chart
 */
function initSeverityChart(severities) {
    const ctx = document.getElementById('severityPolarChart')?.getContext('2d');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: Object.keys(severities),
            datasets: [{
                data: Object.values(severities),
                backgroundColor: [
                    'rgba(239, 68, 68, 0.75)',  // Critical
                    'rgba(249, 115, 22, 0.75)', // High
                    'rgba(234, 179, 8, 0.75)',  // Medium
                    'rgba(59, 130, 246, 0.75)'   // Low
                ],
                borderColor: '#0b1320',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 12 }
                }
            },
            scales: {
                r: {
                    ticks: { display: false },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                }
            }
        }
    });
}
