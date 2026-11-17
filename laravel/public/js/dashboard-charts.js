/**
 * dashboard-charts.js — CivilWatch Admin
 *
 * Charts for the admin dashboard:
 *   #volumeChart      vertical bar   — reports submitted per week / month
 *   #trendAreaChart   filled area    — submitted vs resolved, last 30 days
 *   #categoryMixChart doughnut       — infrastructure vs environmental share
 *
 * Everything is computed from the same /api/admin/citizen-reports list the
 * rest of the dashboard uses, so no analytics endpoint is needed.
 *
 * Usage: DashboardCharts.init()   (loads data, then draws all three)
 *        DashboardCharts.setMode('monthly', buttonEl)
 */
const DashboardCharts = (() => {
    const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const COLORS = {
        primary: '#1A56DB',
        primaryHover: '#0F3FA8',
        resolved: '#10B981',
        others: '#9CA3AF',
        empty: '#E5E7EB',
        grid: '#F3F4F6',
    };

    const TOOLTIP = {
        backgroundColor: '#1F2937',
        titleColor: '#fff',
        bodyColor: '#9CA3AF',
        padding: 10,
        cornerRadius: 8,
    };

    const TREND_DAYS = 30;
    const CATEGORY_COLORS = {
        Infrastructure: COLORS.primary,
        Environmental: COLORS.resolved,
        Others: COLORS.others,
    };

    let reports = [];
    let mode = 'weekly';
    let charts = { volume: null, trend: null, mix: null };

    // ── Helpers ───────────────────────────────────────────────────

    const el = (id) => document.getElementById(id);

    // Local YYYY-MM-DD (toISOString would shift the day near midnight).
    const dayKey = (d) =>
        d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');

    function reportDate(report) {
        const d = new Date(report.submittedAt || report.created_at);
        return isNaN(d) ? null : d;
    }

    function categoryOf(report) {
        const c = (report.category || '').toLowerCase();
        if (c.indexOf('infra') === 0) return 'Infrastructure';
        if (c.indexOf('environ') === 0) return 'Environmental';
        return 'Others';
    }

    // ── Data shaping (pure — no DOM, no Chart) ────────────────────

    function bucketByPeriod(rows, period) {
        const buckets = {};

        rows.forEach((report) => {
            const d = reportDate(report);
            if (!d) return;

            let key;
            if (period === 'weekly') {
                const jan1 = new Date(d.getFullYear(), 0, 1);
                const week = Math.ceil(((d - jan1) / 86400000 + jan1.getDay() + 1) / 7);
                key = d.getFullYear() + '-W' + String(week).padStart(2, '0');
            } else {
                key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            }
            buckets[key] = (buckets[key] || 0) + 1;
        });

        const keys = Object.keys(buckets).sort();

        return {
            labels: keys.map((key) => {
                if (period === 'weekly') return key.replace('-W', ' W');
                const [year, month] = key.split('-');
                return MONTHS[parseInt(month, 10) - 1] + ' ' + year;
            }),
            values: keys.map((key) => buckets[key]),
        };
    }

    function dailySeries(rows, days) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const start = today.getTime() - (days - 1) * 86400000;

        const index = {};
        const labels = [];
        const submitted = [];
        const resolved = [];

        for (let i = 0; i < days; i++) {
            const day = new Date(start + i * 86400000);
            index[dayKey(day)] = i;
            labels.push(MONTHS[day.getMonth()] + ' ' + day.getDate());
            submitted.push(0);
            resolved.push(0);
        }

        rows.forEach((report) => {
            const created = reportDate(report);
            if (created) {
                const i = index[dayKey(created)];
                if (i !== undefined) submitted[i]++;
            }

            if (report.resolvedAt) {
                const done = new Date(report.resolvedAt);
                if (!isNaN(done)) {
                    const i = index[dayKey(done)];
                    if (i !== undefined) resolved[i]++;
                }
            }
        });

        return { labels, submitted, resolved };
    }

    function categoryCounts(rows) {
        const counts = { Infrastructure: 0, Environmental: 0, Others: 0 };
        rows.forEach((report) => counts[categoryOf(report)]++);

        const labels = ['Infrastructure', 'Environmental'];
        if (counts.Others > 0) labels.push('Others');

        return {
            counts,
            labels,
            values: labels.map((label) => counts[label]),
            colors: labels.map((label) => CATEGORY_COLORS[label]),
            total: labels.reduce((sum, label) => sum + counts[label], 0),
        };
    }

    // ── Shared Chart.js options ───────────────────────────────────

    const valueAxis = () => ({
        beginAtZero: true,
        grid: { color: COLORS.grid },
        border: { display: false },
        ticks: { precision: 0 },
    });

    const labelAxis = () => ({
        grid: { display: false },
        border: { display: false },
    });

    const noLegend = () => ({ display: false });

    // ── Renderers ─────────────────────────────────────────────────

    function renderVolume() {
        const canvas = el('volumeChart');
        if (!canvas || typeof Chart === 'undefined') return;
        if (charts.volume) charts.volume.destroy();

        const { labels, values } = bucketByPeriod(reports, mode);
        const empty = labels.length === 0;
        const title = el('volumeChartTitle');
        if (title) {
            title.textContent = 'Reports Submitted (' +
                (mode === 'weekly' ? 'Weekly' : 'Monthly') + ')';
        }

        charts.volume = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: empty ? ['No data yet'] : labels,
                datasets: [{
                    label: 'Reports',
                    data: empty ? [0] : values,
                    backgroundColor: COLORS.primary,
                    hoverBackgroundColor: COLORS.primaryHover,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: noLegend(), tooltip: TOOLTIP },
                scales: { x: labelAxis(), y: valueAxis() },
            },
        });
    }

    function renderTrend() {
        const canvas = el('trendAreaChart');
        if (!canvas || typeof Chart === 'undefined') return;
        if (charts.trend) charts.trend.destroy();

        const { labels, submitted, resolved } = dailySeries(reports, TREND_DAYS);

        charts.trend = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Submitted',
                        data: submitted,
                        borderColor: COLORS.primary,
                        backgroundColor: 'rgba(26,86,219,0.12)',
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Resolved',
                        data: resolved,
                        borderColor: COLORS.resolved,
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        fill: false,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                        },
                    },
                    tooltip: TOOLTIP,
                },
                scales: {
                    x: { ...labelAxis(), ticks: { maxTicksLimit: 8 } },
                    y: valueAxis(),
                },
            },
        });
    }

    function renderMix() {
        const canvas = el('categoryMixChart');
        if (!canvas || typeof Chart === 'undefined') return;
        if (charts.mix) charts.mix.destroy();

        const { counts, labels, values, colors, total } = categoryCounts(reports);

        renderMixLegend(counts, labels, total);

        charts.mix = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: total === 0 ? ['No data'] : labels,
                datasets: [{
                    data: total === 0 ? [1] : values,
                    backgroundColor: total === 0 ? [COLORS.empty] : colors,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: noLegend(), tooltip: TOOLTIP },
            },
        });
    }

    function renderMixLegend(counts, labels, total) {
        const legend = el('categoryMixLegend');
        if (!legend) return;

        const pct = (n) => (total > 0 ? ((n / total) * 100).toFixed(1) + '%' : '0%');

        legend.innerHTML = labels.map((label) =>
            '<div class="legend-item">' +
            '<div class="legend-dot" style="background:' + CATEGORY_COLORS[label] + '"></div>' +
            '<span class="legend-label">' + label + '</span>' +
            '<span class="legend-value">' + pct(counts[label]) + '</span>' +
            '</div>'
        ).join('') +
            '<div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--gray-200);">' +
            '<div style="font-size:22px;font-weight:700;color:var(--gray-900)">' + total + '</div>' +
            '<div style="font-size:11px;color:var(--gray-400)">Total</div></div>';
    }

    // ── Public API ────────────────────────────────────────────────

    function renderAll() {
        renderVolume();
        renderTrend();
        renderMix();
    }

    async function load() {
        try {
            const json = await Api.get('/api/admin/citizen-reports', { limit: 1000 });
            if (!json || !json.success) throw new Error('API error');
            reports = (json.data && json.data.data) ? json.data.data : (json.data || []);
        } catch (err) {
            console.error('Dashboard charts: data load failed', err);
            reports = [];
        }
        renderAll();
    }

    function setMode(next, button) {
        mode = next;
        document.querySelectorAll('.dashboard-charts .toggle-btn').forEach((b) => {
            b.classList.remove('active');
        });
        if (button) button.classList.add('active');
        renderVolume();
    }

    function init() {
        if (typeof Chart === 'undefined') {
            console.warn('Dashboard charts: Chart.js not loaded');
            return;
        }
        Chart.defaults.font.family = "'Inter', 'Roboto', sans-serif";
        Chart.defaults.color = '#6B7280';
        load();
    }

    return { init, load, setMode };
})();
