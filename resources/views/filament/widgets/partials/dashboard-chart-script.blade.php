@once
    @push('scripts')
        <script>
            (function () {
                const registerWhenReady = (fn) => {
                    if (typeof window.Chart !== 'undefined') {
                        try { fn(window.Chart); } catch (e) { console.error(e); }
                        return;
                    }
                    const iv = setInterval(() => {
                        if (typeof window.Chart !== 'undefined') {
                            clearInterval(iv);
                            try { fn(window.Chart); } catch (e) { console.error(e); }
                        }
                    }, 100);
                };

                registerWhenReady((Chart) => {
                    Chart.defaults.animation.duration = 900;
                    Chart.defaults.animation.easing = 'easeOutQuart';

                    const origBar = Chart.controllers.bar;
                    Chart.controllers.bar = class extends origBar {
                        draw() {
                            super.draw(arguments);
                        }
                    };

                    if (!Chart.registry.plugins.get('barValueLabels')) {
                        const helpers = Chart.helpers;
                        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

                        const barValueLabels = {
                            id: 'barValueLabels',
                            afterDatasetsDraw(chart, args, opts) {
                                const options = opts || {};
                                if (options.display === false) return;

                                const orientation = (chart?.config?.options?.indexAxis === 'y' || chart?.options?.indexAxis === 'y')
                                    ? 'horizontal'
                                    : 'vertical';

                                const { ctx } = chart;
                                const padding = options.padding ?? 6;
                                const color = options.color || '#111827';
                                const fontOptions = options.font || { size: 11, weight: '600' };

                                // NEW: hỗ trợ formatter dạng 'raw' (hiện số thô) hoặc mặc định locale vi-VN
                                let formatter = (value) => Number(value).toLocaleString(options.locale || 'vi-VN');
                                if (typeof options.formatter === 'function') {
                                    formatter = options.formatter;
                                } else if (options.formatter === 'raw') {
                                    formatter = (value) => String(Number(value || 0));
                                }

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
                                            if (typeof raw.y === 'number') value = raw.y;
                                            else if (typeof raw.v === 'number') value = raw.v;
                                            else if (typeof raw.x === 'number') value = raw.x;
                                        }

                                        const numericValue = Number(value);
                                        if (!Number.isFinite(numericValue) || numericValue === 0) return;

                                        const label = formatter(numericValue);
                                        if (label === null || typeof label === 'undefined' || label === '') return;

                                        let { x, y } = element;
                                        if (Number.isNaN(x) || Number.isNaN(y)) return;

                                        if (orientation === 'horizontal') {
                                            const chartArea = chart.chartArea;
                                            ctx.textBaseline = typeof options.verticalAlign === 'string' ? options.verticalAlign : 'middle';

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
                        window.dashboardChart = function ({ type = 'bar', data = {}, options = {} }) {
                            return {
                                chart: null,
                                data,
                                options,
                                init() {
                                    const ctx = this.$refs.canvas.getContext('2d');
                                    const cfg = {
                                        type,
                                        data: JSON.parse(JSON.stringify(data || {})),
                                        options: JSON.parse(JSON.stringify(options || {})),
                                    };

                                    const adjustBarThickness = () => {
                                        try {
                                            const w = this.$root.getBoundingClientRect().width || 0;
                                            const dsCount = Array.isArray(cfg.data?.datasets) ? cfg.data.datasets.length : 1;
                                            const base = w < 420 ? 26 : (w < 640 ? 32 : 46);
                                            cfg.options = cfg.options || {};
                                            cfg.options.datasets = cfg.options.datasets || {};
                                            cfg.options.datasets.bar = cfg.options.datasets.bar || {};
                                            cfg.options.datasets.bar.maxBarThickness = base;
                                            if ((cfg.options.indexAxis === 'y') && dsCount > 6 && w < 640) {
                                                cfg.options.datasets.bar.maxBarThickness = Math.max(18, base - 8);
                                            }
                                        } catch (e) {}
                                    };

                                    adjustBarThickness();
                                    this.chart = new Chart(ctx, cfg);

                                    this.$watch('data', (v) => {
                                        if (!this.chart) return;
                                        this.chart.data = JSON.parse(JSON.stringify(v || {}));
                                        this.chart.update();
                                    });

                                    this.$watch('options', (v) => {
                                        if (!this.chart) return;
                                        this.chart.options = Object.assign({}, this.chart.options, JSON.parse(JSON.stringify(v || {})));
                                        this.chart.update();
                                    });

                                    const ro = new ResizeObserver(() => {
                                        if (!this.chart) return;
                                        adjustBarThickness();
                                        this.chart.update('none');
                                    });
                                    ro.observe(this.$root);
                                    this._ro = ro;
                                },
                                destroy() {
                                    if (this._ro) {
                                        try { this._ro.disconnect(); } catch (e) {}
                                        this._ro = null;
                                    }
                                    if (this.chart) {
                                        try { this.chart.destroy(); } catch (e) {}
                                        this.chart = null;
                                    }
                                },
                            }
                        }
                    }
                });
            })();
        </script>
    @endpush
@endonce
