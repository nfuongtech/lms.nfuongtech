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

                    const barValueLabels = {
                        id: 'barValueLabels',
                        afterDatasetsDraw(chart, args, opts) {
                            const options = opts || {};
                            if (options.display === false) {
                                return;
                            }

                            const { ctx } = chart;
                            const padding = options.padding ?? 6;
                            const color = options.color || '#111827';
                            const fontOptions = options.font || { size: 11, weight: '600' };
                            const font = helpers.toFont(fontOptions);

                            ctx.save();
                            ctx.font = font.string;
                            ctx.fillStyle = color;

                            chart.data.datasets.forEach((dataset, datasetIndex) => {
                                const meta = chart.getDatasetMeta(datasetIndex);
                                if (!meta || (typeof chart.isDatasetVisible === 'function' && !chart.isDatasetVisible(datasetIndex))) {
                                    return;
                                }

                                const axisCandidate = dataset.indexAxis ?? chart.options?.indexAxis ?? chart.config.options?.indexAxis;
                                const datasetAxis = axisCandidate || (chart.config.type === 'horizontalBar' ? 'y' : 'x');
                                const isHorizontal = datasetAxis === 'y';

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

                                    if (value === null || Number(value) === 0) {
                                        return;
                                    }

                                    const numericValue = Number(value);

                                    if (isHorizontal) {
                                        let x = element.x;
                                        const y = element.y;

                                        if (Number.isNaN(x) || Number.isNaN(y)) {
                                            return;
                                        }

                                        ctx.textBaseline = 'middle';
                                        ctx.textAlign = numericValue >= 0 ? 'left' : 'right';
                                        x += numericValue >= 0 ? padding : -padding;

                                        if (numericValue >= 0 && x > chart.chartArea.right - 4) {
                                            x = chart.chartArea.right - 4;
                                        }

                                        if (numericValue < 0 && x < chart.chartArea.left + 4) {
                                            x = chart.chartArea.left + 4;
                                        }

                                        ctx.fillText(numericValue.toLocaleString('vi-VN'), x, y);
                                    } else {
                                        const x = element.x;
                                        let y = element.y;

                                        if (Number.isNaN(x) || Number.isNaN(y)) {
                                            return;
                                        }

                                        ctx.textAlign = 'center';

                                        if (numericValue >= 0) {
                                            ctx.textBaseline = 'bottom';
                                            y -= padding;
                                            if (y < chart.chartArea.top + 4) {
                                                y = chart.chartArea.top + 4;
                                            }
                                        } else {
                                            ctx.textBaseline = 'top';
                                            y += padding;
                                            if (y > chart.chartArea.bottom - 4) {
                                                y = chart.chartArea.bottom - 4;
                                            }
                                        }

                                        ctx.fillText(numericValue.toLocaleString('vi-VN'), x, y);
                                    }
                                });
                            });

                            ctx.restore();
                        },
                    };

                    Chart.register(barValueLabels);
                }

                if (typeof window.dashboardChart === 'undefined') {
                    const cloneDeep = (value) => {
                        if (typeof window.structuredClone === 'function') {
                            return window.structuredClone(value);
                        }

                        try {
                            return JSON.parse(JSON.stringify(value));
                        } catch (error) {
                            return value;
                        }
                    };

                    const formatOptions = (options) => {
                        if (!options) {
                            return {};
                        }

                        const cloned = cloneDeep(options) || {};
                        const meta = cloned.__meta || null;

                        if (meta) {
                            delete cloned.__meta;
                        }

                        if (!meta) {
                            return cloned;
                        }

                        const numericAxisKey = cloned.indexAxis === 'y' ? 'x' : 'y';
                        const axis = cloned.scales?.[numericAxisKey];

                        if (axis) {
                            axis.ticks = axis.ticks || {};
                            if (typeof axis.ticks.callback !== 'function') {
                                axis.ticks.callback = (value) => {
                                    const numericValue = Number(value || 0);

                                    if (!Number.isFinite(numericValue)) {
                                        return value;
                                    }

                                    let display = numericValue;

                                    if (meta.tickDivisor) {
                                        display = numericValue / meta.tickDivisor;
                                    }

                                    const locale = meta.tickLocale || 'vi-VN';
                                    const formatted = display.toLocaleString(locale, {
                                        maximumFractionDigits: 1,
                                    });

                                    return meta.tickSuffix ? `${formatted}${meta.tickSuffix}` : formatted;
                                };
                            }
                        }

                        return cloned;
                    };

                    window.dashboardChart = function ({ type = 'bar', data = {}, options = {} }) {
                        return {
                            chartInstance: null,
                            type,
                            data,
                            options,
                            init() {
                                this.$nextTick(() => {
                                    this.renderChart();
                                    this.$watch('data', () => this.refreshChart());
                                    this.$watch('options', () => this.refreshChart());
                                });
                            },
                            renderChart() {
                                if (!this.$refs.canvas || typeof Chart === 'undefined') {
                                    return;
                                }

                                const context = this.$refs.canvas.getContext('2d');

                                if (!context) {
                                    return;
                                }

                                const config = {
                                    type: this.type,
                                    data: cloneDeep(this.data),
                                    options: formatOptions(this.options),
                                };

                                if (this.chartInstance) {
                                    this.chartInstance.destroy();
                                }

                                this.chartInstance = new Chart(context, config);
                            },
                            refreshChart() {
                                if (!this.chartInstance) {
                                    this.renderChart();
                                    return;
                                }

                                this.chartInstance.config.type = this.type;
                                this.chartInstance.data = cloneDeep(this.data);
                                this.chartInstance.options = formatOptions(this.options);
                                this.chartInstance.update('resize');
                            },
                            destroy() {
                                if (this.chartInstance) {
                                    this.chartInstance.destroy();
                                    this.chartInstance = null;
                                }
                            },
                            destroyed() {
                                this.destroy();
                            },
                        };
                    };
                }
            })();
        </script>
    @endpush
@endonce
