import { Chart, CategoryScale, LinearScale, LineController, LineElement, PointElement, Tooltip, Filler } from 'chart.js';

Chart.register(CategoryScale, LinearScale, LineController, LineElement, PointElement, Tooltip, Filler);

function chartColors() {
    const style = getComputedStyle(document.documentElement);
    return {
        success: style.getPropertyValue('--chart-success').trim(),
        error: style.getPropertyValue('--chart-error').trim(),
        fill: style.getPropertyValue('--chart-fill').trim(),
        grid: style.getPropertyValue('--line').trim(),
        text: style.getPropertyValue('--muted').trim(),
    };
}

export default function requestChart(initialSeries) {
    let chart;

    return {
        init() {
            const colors = chartColors();
            const format = new Intl.NumberFormat('en');
            chart = new Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels: initialSeries?.labels ?? [],
                    datasets: [
                        { label: 'Successful', data: initialSeries?.success ?? [], borderColor: colors.success, backgroundColor: colors.fill, fill: 'origin' },
                        { label: 'Failed', data: initialSeries?.errors ?? [], borderColor: colors.error, borderDash: [5, 3], fill: false },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
                    elements: { line: { borderWidth: 1.7, tension: 0.3 }, point: { radius: 0, hoverRadius: 4 } },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { color: colors.grid },
                            ticks: {
                                color: colors.text,
                                maxRotation: 0,
                                maxTicksLimit: 8,
                                autoSkipPadding: 30,
                                font: { family: 'Geist Mono Variable', size: 11 },
                                callback(value) {
                                    return this.getLabelForValue(value).split(', ')[1];
                                },
                            },
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: colors.grid, drawTicks: false },
                            ticks: {
                                color: colors.text,
                                maxTicksLimit: 5,
                                padding: 10,
                                font: { family: 'Geist Mono Variable', size: 11 },
                                callback: (value) => format.format(value),
                            },
                        },
                    },
                    plugins: {
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                title: ([item]) => item ? item.label + ' UTC' : '',
                                label: (item) => item.dataset.label + ': ' + format.format(item.raw),
                                footer: (items) => 'Total: ' + format.format(items.reduce((sum, item) => sum + item.raw, 0)),
                            },
                        },
                    },
                },
            });
        },
        update(series) {
            if (!chart) return;
            chart.data.labels = series?.labels ?? [];
            chart.data.datasets[0].data = series?.success ?? [];
            chart.data.datasets[1].data = series?.errors ?? [];
            chart.update('none');
        },
        recolor() {
            if (!chart) return;
            const colors = chartColors();
            chart.data.datasets[0].borderColor = colors.success;
            chart.data.datasets[0].backgroundColor = colors.fill;
            chart.data.datasets[1].borderColor = colors.error;
            chart.options.scales.x.border.color = colors.grid;
            chart.options.scales.x.ticks.color = colors.text;
            chart.options.scales.y.grid.color = colors.grid;
            chart.options.scales.y.ticks.color = colors.text;
            chart.update('none');
        },
        destroy() {
            chart?.destroy();
        },
    };
}
