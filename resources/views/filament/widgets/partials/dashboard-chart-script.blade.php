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
                                    const yCenter = element.y;
                                    const indexAxis = chart?.config?.options?.indexAxis || 'x';
                                    let drawX = x;
                                    let drawY = yCenter;

                                    if (Number.isNaN(x) || Number.isNaN(yCenter)) {
                                        return;
                                    }

                                    if (indexAxis === 'y') {
                                        ctx.textBaseline = 'middle';
                                        if (numericValue >= 0) {
                                            ctx.textAlign = 'left';
                                            drawX += padding;
                                            if (drawX > chart.chartArea.right - 4) {
                                                drawX = chart.chartArea.right - 4;
                                                ctx.textAlign = 'right';
                                            }
                                        } else {
                                            ctx.textAlign = 'right';
                                            drawX -= padding;
                                            if (drawX < chart.chartArea.left + 4) {
                                                drawX = chart.chartArea.left + 4;
                                                ctx.textAlign = 'left';
                                            }
                                        }
                                    } else {
                                        ctx.textAlign = 'center';
                                        if (numericValue >= 0) {
                                            ctx.textBaseline = 'bottom';
                                            drawY = yCenter - padding;
                                            if (drawY < chart.chartArea.top + 4) {
                                                drawY = chart.chartArea.top + 4;
                                                ctx.textBaseline = 'top';
                                            }
                                        } else {
                                            ctx.textBaseline = 'top';
                                            drawY = yCenter + padding;
                                            if (drawY > chart.chartArea.bottom - 4) {
                                                drawY = chart.chartArea.bottom - 4;
                                                ctx.textBaseline = 'bottom';
                                            }
                                        }
                                    }

                                    ctx.fillText(numericValue.toLocaleString('vi-VN'), drawX, drawY);
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
