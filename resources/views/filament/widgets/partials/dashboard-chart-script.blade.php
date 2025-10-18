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
                            ctx.textAlign = 'center';

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

                                    if (value === null || Number(value) === 0) {
                                        return;
                                    }

                                    const numericValue = Number(value);
                                    const x = element.x;
                                    let y = element.y;

                                    if (Number.isNaN(x) || Number.isNaN(y)) {
                                        return;
                                    }

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
