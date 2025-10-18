@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.__dashboardChartRegistered) {
                    return;
                }

                window.__dashboardChartRegistered = true;

                const buildGradient = (ctx, color) => {
                    const match = /rgba?\(([^)]+)\)/.exec(color);

                    if (! match) {
                        return color;
                    }

                    const stops = match[1].split(',').map(part => part.trim());
                    const alpha = stops.length === 4 ? parseFloat(stops[3]) : 0.85;
                    const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);

                    gradient.addColorStop(0, `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${alpha})`);
                    gradient.addColorStop(1, `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${Math.max(alpha - 0.55, 0.1)})`);

                    return gradient;
                };

                Alpine.data('dashboardChart', ({ type = 'bar', data, options }) => ({
                    chartInstance: null,
                    type,
                    chartData: data,
                    chartOptions: options,
                    init() {
                        this.$watch('chartData', () => this.refresh());
                        this.$watch('chartOptions', () => this.refresh());
                        this.render();
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
                        cloned.plugins.tooltip.callbacks = cloned.plugins.tooltip.callbacks || {};

                        if (! cloned.plugins.tooltip.callbacks.label) {
                            cloned.plugins.tooltip.callbacks.label = (context) => {
                                const datasetLabel = context.dataset?.label ?? '';
                                const value = context.parsed?.y ?? context.parsed ?? 0;
                                const locale = meta.tooltipLocale ?? 'vi-VN';
                                const suffix = meta.tooltipSuffix ?? '';
                                const formatted = new Intl.NumberFormat(locale).format(value);

                                return `${datasetLabel}: ${formatted}${suffix}`.trim();
                            };
                        }

                        cloned.scales = cloned.scales || {};
                        cloned.scales.y = cloned.scales?.y || {};
                        cloned.scales.y.ticks = cloned.scales.y.ticks || {};

                        if (! cloned.scales.y.ticks.callback) {
                            const divisor = meta.tickDivisor ?? null;
                            const tickLocale = meta.tickLocale ?? 'vi-VN';
                            const tickSuffix = meta.tickSuffix ?? '';

                            cloned.scales.y.ticks.callback = (value) => {
                                const normalized = divisor ? value / divisor : value;
                                const formatted = new Intl.NumberFormat(tickLocale).format(normalized);

                                return `${formatted}${tickSuffix}`.trim();
                            };
                        }

                        return cloned;
                    },
                    prepareData(ctx) {
                        if (! this.chartData?.datasets) {
                            return this.chartData;
                        }

                        const datasets = this.chartData.datasets.map(dataset => ({
                            ...dataset,
                            backgroundColor: Array.isArray(dataset.backgroundColor)
                                ? dataset.backgroundColor.map(color => buildGradient(ctx, color))
                                : buildGradient(ctx, dataset.backgroundColor ?? 'rgba(59, 130, 246, 0.85)'),
                        }));

                        return {
                            ...this.chartData,
                            datasets,
                        };
                    },
                    render() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        const preparedData = this.prepareData(ctx);
                        const preparedOptions = this.prepareOptions();
                        const config = {
                            type: this.type,
                            data: preparedData,
                            options: preparedOptions,
                        };

                        if (this.chartInstance) {
                            this.chartInstance.destroy();
                        }

                        this.chartInstance = new Chart(ctx, config);
                    },
                    refresh() {
                        if (! this.chartInstance) {
                            this.render();
                            return;
                        }

                        const ctx = this.$refs.canvas.getContext('2d');
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
