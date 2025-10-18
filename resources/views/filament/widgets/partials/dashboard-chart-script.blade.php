@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.__dashboardChartRegistered) {
                    return;
                }

                window.__dashboardChartRegistered = true;

                Alpine.data('dashboardChart', ({ type = 'bar', data, options }) => ({
                    chartInstance: null,
                    type,
                    chartData: data,
                    chartOptions: options,
                    init() {
                        this.$nextTick(() => this.render());
                        this.$watch('chartData', () => this.refresh());
                        this.$watch('chartOptions', () => this.refresh());
                    },
                    destroy() {
                        if (this.chartInstance) {
                            this.chartInstance.destroy();
                            this.chartInstance = null;
                        }
                    },
                    cloneOptions() {
                        if (typeof structuredClone === 'function') {
                            return structuredClone(this.chartOptions ?? {});
                        }

                        try {
                            return JSON.parse(JSON.stringify(this.chartOptions ?? {}));
                        } catch (error) {
                            return this.chartOptions ?? {};
                        }
                    },
                    prepareOptions() {
                        const cloned = this.cloneOptions();
                        const meta = cloned.__meta ?? {};

                        if (cloned.__meta) {
                            delete cloned.__meta;
                        }

                        cloned.plugins = cloned.plugins || {};
                        cloned.plugins.tooltip = cloned.plugins.tooltip || {};
                        cloned.plugins.tooltip.callbacks = {
                            label(context) {
                                const datasetLabel = context.dataset?.label ?? '';
                                const value = context.parsed?.y ?? 0;
                                const locale = meta.tooltipLocale ?? 'vi-VN';
                                const suffix = meta.tooltipSuffix ?? '';
                                const formatted = new Intl.NumberFormat(locale).format(value);

                                return `${datasetLabel}: ${formatted}${suffix}`.trim();
                            },
                        };

                        cloned.scales = cloned.scales || {};
                        cloned.scales.y = cloned.scales?.y || {};
                        cloned.scales.y.ticks = cloned.scales.y.ticks || {};

                        const divisor = meta.tickDivisor ?? null;
                        const tickLocale = meta.tickLocale ?? 'vi-VN';
                        const tickSuffix = meta.tickSuffix ?? '';

                        cloned.scales.y.ticks.callback = (value) => {
                            const numeric = typeof value === 'number' ? value : parseFloat(value ?? 0);
                            const normalized = divisor ? numeric / divisor : numeric;
                            const formatted = new Intl.NumberFormat(tickLocale).format(normalized);

                            return `${formatted}${tickSuffix}`.trim();
                        };

                        return cloned;
                    },
                    prepareData(ctx) {
                        if (! this.chartData?.datasets) {
                            return this.chartData;
                        }

                        const gradient = (color) => {
                            const match = /rgba?\(([^)]+)\)/.exec(color ?? '');

                            if (! match) {
                                return color;
                            }

                            const stops = match[1].split(',').map(part => part.trim());
                            const alpha = stops.length === 4 ? parseFloat(stops[3]) : 0.85;
                            const start = `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${Math.min(alpha + 0.1, 1)})`;
                            const end = `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${Math.max(alpha - 0.55, 0.12)})`;

                            const gradientFill = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
                            gradientFill.addColorStop(0, start);
                            gradientFill.addColorStop(1, end);

                            return gradientFill;
                        };

                        const datasets = this.chartData.datasets.map(dataset => ({
                            ...dataset,
                            backgroundColor: Array.isArray(dataset.backgroundColor)
                                ? dataset.backgroundColor.map(color => gradient(color))
                                : gradient(dataset.backgroundColor),
                        }));

                        return {
                            ...this.chartData,
                            datasets,
                        };
                    },
                    render() {
                        const canvas = this.$refs.canvas;

                        if (! canvas) {
                            return;
                        }

                        const ctx = canvas.getContext('2d');

                        if (! ctx) {
                            return;
                        }

                        const preparedData = this.prepareData(ctx);
                        const preparedOptions = this.prepareOptions();

                        this.destroy();
                        this.chartInstance = new Chart(ctx, {
                            type: this.type,
                            data: preparedData,
                            options: preparedOptions,
                        });
                    },
                    refresh() {
                        if (! this.chartInstance) {
                            this.render();
                            return;
                        }

                        const ctx = this.$refs.canvas?.getContext('2d');

                        if (! ctx) {
                            this.render();
                            return;
                        }

                        const preparedData = this.prepareData(ctx);
                        const preparedOptions = this.prepareOptions();

                        this.chartInstance.data = preparedData;
                        this.chartInstance.options = preparedOptions;
                        this.chartInstance.update('active');
                    },
                }));
            });
        </script>
    @endpush
@endonce
