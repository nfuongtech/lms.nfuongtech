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
                            const showZero = options.showZero === true;
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

                                    if (!Number.isFinite(numericValue) || (numericValue === 0 && !showZero)) {
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

                if (typeof window.dashboardChart !== 'function') {
                    window.dashboardChart = function ({ type = 'bar', data = {}, options = {} } = {}) {
                        return {
                            chartInstance: null,
                            chartType: type,
                            chartData: data,
                            chartOptions: options,

                            init() {
                                this.$nextTick(() => this.renderChart());

                                this.$watch('chartData', (value) => this.updateChart(value, this.chartOptions));
                                this.$watch('chartOptions', (value) => this.updateChart(this.chartData, value));
                                this.$watch('chartType', () => this.reinitialize());
                            },

                            destroy() {
                                if (this.chartInstance) {
                                    this.chartInstance.destroy();
                                    this.chartInstance = null;
                                }
                            },

                            reinitialize() {
                                this.destroy();
                                this.$nextTick(() => this.renderChart());
                            },

                            normalizeData(payload) {
                                if (!payload || typeof payload !== 'object') {
                                    return { labels: [], datasets: [] };
                                }

                                return {
                                    labels: Array.isArray(payload.labels) ? payload.labels : [],
                                    datasets: Array.isArray(payload.datasets) ? payload.datasets : [],
                                };
                            },

                            normalizeOptions(payload) {
                                return payload && typeof payload === 'object' ? payload : {};
                            },

                            renderChart() {
                                if (!this.$refs.canvas || typeof window.Chart === 'undefined') {
                                    return;
                                }

                                const context = this.$refs.canvas.getContext('2d');
                                if (!context) {
                                    return;
                                }

                                this.chartInstance = new window.Chart(context, {
                                    type: this.chartType,
                                    data: this.normalizeData(this.chartData),
                                    options: this.normalizeOptions(this.chartOptions),
                                });
                            },

                            updateChart(data, options) {
                                if (!this.chartInstance) {
                                    this.renderChart();
                                    return;
                                }

                                this.chartInstance.config.type = this.chartType;
                                this.chartInstance.data = this.normalizeData(data);
                                this.chartInstance.options = this.normalizeOptions(options);
                                this.chartInstance.update();
                            },
                        };
                    };
                }
            })();
        </script>
    @endpush
@endonce
