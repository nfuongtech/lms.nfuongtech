@once
    @push('scripts')
        <script>
            (function () {
                if (typeof window.Chart === 'undefined') return;

                const Chart = window.Chart;

                const deepClone = (value) => {
                    if (Array.isArray(value)) {
                        return value.map(deepClone);
                    }

                    if (value && typeof value === 'object') {
                        return Object.keys(value).reduce((carry, key) => {
                            carry[key] = deepClone(value[key]);
                            return carry;
                        }, {});
                    }

                    return value;
                };

                const datasetValueTooltipFactory = (config = {}) => {
                    const axis = (config.axis || 'y').toLowerCase();
                    const locale = config.locale || 'vi-VN';
                    const prefix = config.prefix || '';
                    const suffix = config.suffix || '';

                    return (ctx) => {
                        if (!ctx) {
                            return '';
                        }

                        const dataset = ctx.dataset || {};
                        const datasetLabel = typeof dataset.label !== 'undefined' ? dataset.label : '';
                        const parsed = typeof ctx.parsed !== 'undefined' && ctx.parsed !== null ? ctx.parsed : {};
                        const raw = axis === 'x' ? parsed.x : parsed.y;
                        const numeric = Number(raw !== undefined && raw !== null ? raw : 0);

                        if (!Number.isFinite(numeric)) {
                            return datasetLabel ? datasetLabel + ': 0' : '0';
                        }

                        const formatted = prefix + numeric.toLocaleString(locale) + suffix;

                        return datasetLabel ? datasetLabel + ': ' + formatted : formatted;
                    };
                };

                const stackedSumTooltipFactory = (config = {}) => {
                    const targetStack = typeof config.stack !== 'undefined' ? config.stack : null;
                    const locale = config.locale || 'vi-VN';
                    const label = config.label || '';
                    const prefix = config.prefix || '';
                    const suffix = config.suffix || '';

                    return (items) => {
                        if (!Array.isArray(items) || !items.length) {
                            return '';
                        }

                        const firstItem = items[0] || {};
                        const dataIndex = typeof firstItem.dataIndex !== 'undefined' ? firstItem.dataIndex : 0;
                        const chart = firstItem.chart;
                        if (!chart || !chart.data || !Array.isArray(chart.data.datasets)) {
                            return '';
                        }

                        const sum = chart.data.datasets.reduce((carry, dataset) => {
                            if (targetStack && dataset.stack !== targetStack) {
                                return carry;
                            }

                            const datasetData = Array.isArray(dataset.data)
                                ? dataset.data
                                : [dataset.data];
                            const raw = datasetData[dataIndex];
                            const numeric = Number(raw !== undefined && raw !== null ? raw : 0);

                            if (!Number.isFinite(numeric)) {
                                return carry;
                            }

                            return carry + numeric;
                        }, 0);

                        if (!sum) {
                            return '';
                        }

                        const formatted = prefix + sum.toLocaleString(locale) + suffix;

                        return label ? label + ': ' + formatted : formatted;
                    };
                };

                const resolveTooltipCallback = (config) => {
                    if (typeof config === 'function') {
                        return config;
                    }

                    if (!config || typeof config !== 'object') {
                        return config;
                    }

                    const type = String(config.type || config.mode || '').toLowerCase();

                    if (type === 'dataset-value') {
                        return datasetValueTooltipFactory(config);
                    }

                    if (type === 'stacked-sum') {
                        return stackedSumTooltipFactory(config);
                    }

                    return config;
                };

                const applyTooltipCallbacks = (callbacks) => {
                    if (!callbacks || typeof callbacks !== 'object') {
                        return callbacks;
                    }

                    Object.keys(callbacks).forEach((key) => {
                        callbacks[key] = resolveTooltipCallback(callbacks[key]);
                    });

                    return callbacks;
                };

                const transformChartOptions = (options) => {
                    if (!options || typeof options !== 'object') {
                        return options || {};
                    }

                    const plugins = options.plugins || {};

                    if (plugins.tooltip && plugins.tooltip.callbacks) {
                        plugins.tooltip.callbacks = applyTooltipCallbacks(plugins.tooltip.callbacks);
                    }

                    return options;
                };

                const buildValueFormatter = (formatterOption, pluginOptions = {}) => {
                    if (typeof formatterOption === 'function') {
                        return formatterOption;
                    }

                    const fallbackLocale = pluginOptions.locale || 'vi-VN';

                    const resolveIntlFormatter = (config, { style, currency } = {}) => {
                        const locale = config.locale || fallbackLocale;
                        const minimumFractionDigits = typeof config.minimumFractionDigits !== 'undefined'
                            ? config.minimumFractionDigits
                            : 0;
                        const maximumFractionDigits = typeof config.maximumFractionDigits !== 'undefined'
                            ? config.maximumFractionDigits
                            : minimumFractionDigits;

                        return new Intl.NumberFormat(locale, {
                            style,
                            currency,
                            minimumFractionDigits,
                            maximumFractionDigits,
                        });
                    };

                    if (formatterOption && typeof formatterOption === 'object') {
                        const type = String(formatterOption.type || formatterOption.mode || formatterOption.format || '').toLowerCase();
                        const prefix = formatterOption.prefix || '';
                        const suffix = formatterOption.suffix || '';

                        if (type === 'currency') {
                            const currency = formatterOption.currency || 'VND';
                            const intl = resolveIntlFormatter(formatterOption, { style: 'currency', currency });
                            return (value) => `${prefix}${intl.format(value)}${suffix}`;
                        }

                        const intl = resolveIntlFormatter(formatterOption, {});
                        return (value) => `${prefix}${intl.format(value)}${suffix}`;
                    }

                    if (typeof formatterOption === 'string') {
                        const normalized = formatterOption.toLowerCase();
                        if (normalized === 'currency') {
                            const intl = resolveIntlFormatter({ locale: fallbackLocale }, { style: 'currency', currency: 'VND' });
                            return (value) => intl.format(value);
                        }

                        if (normalized === 'number') {
                            const intl = resolveIntlFormatter({ locale: fallbackLocale }, {});
                            return (value) => intl.format(value);
                        }
                    }

                    return (value) => {
                        const numeric = Number(value !== undefined && value !== null ? value : 0);
                        return numeric.toLocaleString(fallbackLocale);
                    };
                };

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

                            const hasYAxisIndex = chart && chart.config && chart.config.options && chart.config.options.indexAxis === 'y';
                            const hasOptionsIndex = chart && chart.options && chart.options.indexAxis === 'y';
                            const orientation = (hasYAxisIndex || hasOptionsIndex)
                                ? 'horizontal'
                                : 'vertical';

                            const { ctx } = chart;
                            const padding = typeof options.padding !== 'undefined' ? options.padding : 6;
                            const color = options.color || '#111827';
                            const fontOptions = options.font || { size: 11, weight: '600' };
                            const showZero = options.showZero === true;
                    const valueFormatter = buildValueFormatter(options.formatter, options);
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

                                    const label = valueFormatter(numericValue, {
                                        chart,
                                        dataset,
                                        datasetIndex,
                                        dataIndex: index,
                                        orientation,
                                    });
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
                                if (!payload || typeof payload !== 'object') {
                                    return {};
                                }

                                const cloned = deepClone(payload);

                                return transformChartOptions(cloned);
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
