@once
    @push('scripts')
        <script>
            (function () {
                if (typeof window.Chart === 'undefined') return;

                const Chart = window.Chart;

                // Tuỳ chỉnh mặc định nhẹ cho biểu đồ cột
                Chart.defaults.animation.duration = 900;
                Chart.defaults.animation.easing = 'easeOutQuart';

                // Bo góc cột mặc định nếu là bar
                const origBar = Chart.controllers.bar;
                Chart.controllers.bar = class extends origBar {
                    draw() {
                        super.draw(arguments);
                        // (đã set borderRadius ở datasets trong PHP)
                    }
                };

                if (!Chart.registry.plugins.get('barValueLabels')) {
                    const helpers = Chart.helpers;

                    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

                    const barValueLabels = {
                        id: 'barValueLabels',
                        afterDatasetsDraw(chart, args, opts) {
                            const options = opts || {};
                            if (options.display === false) {
                                return;
                            }

                            const orientation = (chart?.config?.options?.indexAxis === 'y' || chart?.options?.indexAxis === 'y')
                                ? 'horizontal'
                                : 'vertical';

                            const { ctx } = chart;
                            const padding = options.padding ?? 6;
                            const color = options.color || '#111827';
                            const fontOptions = options.font || { size: 11, weight: '600' };
                            const formatter = typeof options.formatter === 'function'
                                ? options.formatter
                                : (value) => Number(value).toLocaleString(options.locale || 'vi-VN');
                            const font = helpers.toFont(fontOptions);

                            ctx.save();
                            ctx.font = font.string;
                            ctx.fillStyle = color;

                            chart.data.datasets.forEach((dataset, datasetIndex) => {
                                const meta = chart.getDatasetMeta(datasetIndex);
                                if (!meta || (typeof chart.isDatasetVisible === 'function' && !chart.isDatasetVisible(datasetIndex))) {
                                    return;
                                }

                                meta.data.forEach((element, index) => {
                                    const raw = Array.isArray(dataset.data) ? dataset.data[index] : dataset.data;
                                    let value = null;

                                    if (typeof raw === 'number') {
                                        value = raw;
                                    } else if (raw && typeof raw === 'object') {
                                        if (typeof raw.y === 'number') {
                                            value = raw.y;
                                        } else if (typeof raw.v === 'number') {
                                            value = raw.v;
                                        }
                                    }

                                    const numericValue = Number(value);

                                    if (!Number.isFinite(numericValue) || numericValue === 0) {
                                        return;
                                    }

                                    const label = formatter(numericValue);
                                    if (label === null || typeof label === 'undefined' || label === '') {
                                        return;
                                    }

                                    let { x, y } = element;

                                    if (Number.isNaN(x) || Number.isNaN(y)) {
                                        return;
                                    }

                                    if (orientation === 'horizontal') {
                                        const chartArea = chart.chartArea;
                                        ctx.textBaseline = typeof options.verticalAlign === 'string'
                                            ? options.verticalAlign
                                            : 'middle';

                                        if (numericValue >= 0) {
                                            ctx.textAlign = typeof options.align === 'string' ? options.align : 'left';
                                            x += padding;
                                            if (x > chartArea.right - 4) {
                                                x = chartArea.right - 4;
                                                ctx.textAlign = 'right';
                                            }
                                        } else {
                                            ctx.textAlign = typeof options.negativeAlign === 'string' ? options.negativeAlign : 'right';
                                            x -= padding;
                                            if (x < chartArea.left + 4) {
                                                x = chartArea.left + 4;
                                                ctx.textAlign = 'left';
                                            }
                                        }

                                        y = clamp(y, chartArea.top + 4, chartArea.bottom - 4);
                                    } else {
                                        ctx.textAlign = 'center';

                                        if (numericValue >= 0) {
                                            ctx.textBaseline = typeof options.verticalAlign === 'string' ? options.verticalAlign : 'bottom';
                                            y -= padding;
                                            if (y < chart.chartArea.top + 4) {
                                                y = chart.chartArea.top + 4;
                                                ctx.textBaseline = 'top';
                                            }
                                        } else {
                                            ctx.textBaseline = typeof options.negativeBaseline === 'string' ? options.negativeBaseline : 'top';
                                            y += padding;
                                            if (y > chart.chartArea.bottom - 4) {
                                                y = chart.chartArea.bottom - 4;
                                                ctx.textBaseline = 'bottom';
                                            }
                                        }

                                        x = clamp(x, chart.chartArea.left + 4, chart.chartArea.right - 4);
                                    }

                                    ctx.fillText(label, x, y);
                                });
                            });

                            ctx.restore();
                        },
                    };

                    Chart.register(barValueLabels);
                }
            })();
        </script>
    @endpush
@endonce
