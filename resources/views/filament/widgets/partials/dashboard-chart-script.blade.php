@once
    @push('styles')
        <style>
            /* Bắt canvas + wrapper luôn full-width trong thẻ card của Filament */
            .fi-wi-chart { width: 100% !important; min-width: 0; }
            .fi-wi-chart canvas { width: 100% !important; max-width: 100% !important; display: block; }
            .fi-widget canvas { width: 100% !important; max-width: 100% !important; display: block; }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const extractValue = (value) => {
                    if (value === null || value === undefined) {
                        return null;
                    }

                    if (typeof value === 'number' && Number.isFinite(value)) {
                        return value;
                    }

                    if (typeof value === 'object') {
                        if (value === null) {
                            return null;
                        }

                        if (typeof value.y === 'number' && Number.isFinite(value.y)) {
                            return value.y;
                        }

                        if (typeof value.value === 'number' && Number.isFinite(value.value)) {
                            return value.value;
                        }
                    }

                    const numeric = Number(value);

                    return Number.isFinite(numeric) ? numeric : null;
                };

                const mergeFonts = (baseFont = {}, overrideFont = {}) => ({
                    family: overrideFont.family ?? baseFont.family ?? 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont',
                    size: overrideFont.size ?? baseFont.size ?? 12,
                    weight: overrideFont.weight ?? baseFont.weight ?? '600',
                    style: overrideFont.style ?? baseFont.style ?? 'normal',
                });

                const toFontString = (font) => {
                    const style = font.style ?? 'normal';
                    const weight = font.weight ?? 'normal';
                    const size = font.size ?? 12;
                    const family = font.family ?? 'sans-serif';

                    return `${style} ${weight} ${size}px ${family}`;
                };

                const formatValue = (value, pluginOpts, datasetOpts) => {
                    const formatter = datasetOpts.formatter ?? pluginOpts.formatter;
                    const locale = datasetOpts.locale ?? pluginOpts.locale ?? 'en-US';

                    if (formatter && typeof formatter === 'object') {
                        if (formatter.type === 'number') {
                            const { type: _ignored, locale: formatterLocale, ...numberOpts } = formatter;

                            try {
                                return new Intl.NumberFormat(formatterLocale ?? locale, numberOpts).format(value);
                            } catch (error) {
                                return value;
                            }
                        }
                    }

                    try {
                        return new Intl.NumberFormat(locale, { maximumFractionDigits: 0 }).format(value);
                    } catch (error) {
                        return value;
                    }
                };

                const barValueLabelsPlugin = {
                    id: 'barValueLabels',
                    afterDatasetsDraw(chart, _args, opts) {
                        const ctx = chart.ctx;

                        if (!ctx) {
                            return;
                        }

                        const pluginOpts = {
                            padding: opts?.padding ?? 6,
                            color: opts?.color ?? '#111827',
                            align: opts?.align ?? 'center',
                            showZero: opts?.showZero ?? false,
                            font: opts?.font ?? {},
                            locale: opts?.locale ?? 'en-US',
                            formatter: opts?.formatter ?? null,
                        };

                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            if (!Array.isArray(dataset?.data)) {
                                return;
                            }

                            const meta = chart.getDatasetMeta(datasetIndex);

                            if (!meta || meta.hidden || (typeof chart.isDatasetVisible === 'function' && !chart.isDatasetVisible(datasetIndex))) {
                                return;
                            }

                            meta.data.forEach((element, dataIndex) => {
                                if (!element) {
                                    return;
                                }

                                const rawValue = dataset.data[dataIndex];
                                const value = extractValue(rawValue);

                                if (value === null) {
                                    return;
                                }

                                const datasetOpts = dataset.barValueLabels ?? {};
                                const showZero = datasetOpts.showZero ?? pluginOpts.showZero;

                                if (!showZero && value === 0) {
                                    return;
                                }

                                const text = formatValue(value, pluginOpts, datasetOpts);

                                if (text === null || text === undefined || text === '') {
                                    return;
                                }

                                const padding = datasetOpts.padding ?? pluginOpts.padding;
                                const align = datasetOpts.align ?? pluginOpts.align;
                                const color = datasetOpts.color ?? pluginOpts.color;
                                const font = mergeFonts(pluginOpts.font, datasetOpts.font ?? {});
                                const fontString = toFontString(font);
                                const position = element.tooltipPosition();
                                const isNegative = value < 0;
                                const y = position.y + (isNegative ? padding : -padding);

                                ctx.save();
                                ctx.fillStyle = color;
                                ctx.font = fontString;
                                ctx.textAlign = align;
                                ctx.textBaseline = isNegative ? 'top' : 'bottom';
                                ctx.fillText(text, position.x, y);
                                ctx.restore();
                            });
                        });
                    },
                };

                const registerBarValueLabelsPlugin = () => {
                    if (typeof Chart === 'undefined' || typeof Chart.register !== 'function') {
                        return;
                    }

                    const registry = Chart.registry ?? Chart._registry ?? null;
                    const alreadyRegistered = registry?.plugins?.get?.('barValueLabels');

                    if (!alreadyRegistered) {
                        Chart.register(barValueLabelsPlugin);
                    }
                };

                const initDashboardChart = () => {
                    if (!window.Alpine) {
                        return;
                    }

                    window.Alpine.data('dashboardChart', (initialConfig = {}) => ({
                        chartInstance: null,
                        canvas: null,
                        isReady: false,
                        pendingConfig: null,
                        type: initialConfig.type ?? 'bar',
                        data: initialConfig.data ?? { labels: [], datasets: [] },
                        options: initialConfig.options ?? {},

                        init() {
                            this.canvas = this.$refs.canvas ?? null;
                            this.isReady = true;

                            if (this.pendingConfig) {
                                const config = this.pendingConfig;
                                this.pendingConfig = null;
                                this.refresh(config);
                                return;
                            }

                            this.render();
                        },

                        refresh(config = {}) {
                            if (!this.isReady) {
                                this.pendingConfig = config;
                                return;
                            }

                            if (config.type) {
                                this.type = config.type;
                            }

                            if (config.data) {
                                this.data = config.data;
                            }

                            if (config.options) {
                                this.options = config.options;
                            }

                            this.render();
                        },

                        mergeOptions(options = {}) {
                            const merged = {
                                responsive: true,
                                maintainAspectRatio: false,
                                ...options,
                            };

                            merged.plugins = {
                                ...(options.plugins ?? {}),
                            };

                            merged.plugins.barValueLabels = merged.plugins.barValueLabels ?? {};

                            return merged;
                        },

                        render() {
                            if (!this.canvas) {
                                return;
                            }

                            if (typeof Chart === 'undefined' || typeof Chart.register !== 'function') {
                                requestAnimationFrame(() => this.render());
                                return;
                            }

                            registerBarValueLabelsPlugin();

                            const context = this.canvas.getContext('2d');

                            if (!context) {
                                return;
                            }

                            const preparedOptions = this.mergeOptions(this.options ?? {});

                            if (this.chartInstance) {
                                this.chartInstance.config.type = this.type;
                                this.chartInstance.data = this.data ?? { labels: [], datasets: [] };
                                this.chartInstance.options = preparedOptions;
                                this.chartInstance.update();
                                return;
                            }

                            this.chartInstance = new Chart(context, {
                                type: this.type,
                                data: this.data ?? { labels: [], datasets: [] },
                                options: preparedOptions,
                            });

                            requestAnimationFrame(() => this.chartInstance?.resize());
                        },

                        destroy() {
                            if (this.chartInstance) {
                                this.chartInstance.destroy();
                                this.chartInstance = null;
                            }
                        },
                    }));
                };

                if (window.Alpine) {
                    initDashboardChart();
                } else {
                    document.addEventListener('alpine:init', initDashboardChart, { once: true });
                }

                const triggerResize = () => window.dispatchEvent(new Event('resize'));

                document.addEventListener('livewire:navigated', () => requestAnimationFrame(triggerResize));
                window.addEventListener('orientationchange', () => setTimeout(triggerResize, 50));

                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('message.processed', () => requestAnimationFrame(triggerResize));
                });
            })();
        </script>
    @endpush
@endonce
