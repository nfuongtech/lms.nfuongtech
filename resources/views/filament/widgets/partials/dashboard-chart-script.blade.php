{{-- resources/views/filament/widgets/partials/dashboard-chart-script.blade.php --}}
@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" integrity="sha384-vk9Qwh6uk9nYy1F/0AQ1H+0SShji3VXjmdJ2vOjXBFWd7Kvd/vA86wnahjgWMAbP" crossorigin="anonymous"></script>
    @endpush

    @push('styles')
        <style>
            /* Đảm bảo canvas full-width trong card Filament */
            .fi-wi-chart,
            .fi-widget canvas {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0;
                display: block;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function registerBarValueLabelsPlugin() {
                if (typeof window === 'undefined') {
                    return;
                }

                const attemptRegister = () => {
                    if (typeof window.Chart === 'undefined') {
                        setTimeout(attemptRegister, 200);
                        return;
                    }

                    const Chart = window.Chart;

                    if (Chart?.registry?.plugins?.get('barValueLabels')) {
                        return;
                    }

                    const BarValueLabelsPlugin = {
                        id: 'barValueLabels',

                        afterDatasetsDraw(chart, args, pluginOptions) {
                            const { ctx, chartArea } = chart ?? {};

                            if (!ctx || !chartArea || pluginOptions?.display === false) {
                                return;
                            }

                            const defaults = {
                                padding: 10,
                                color: '#0f172a',
                                font: {
                                    size: 11,
                                    weight: '600',
                                    family: 'system-ui, -apple-system, sans-serif',
                                },
                                showZero: true,
                                locale: 'vi-VN',
                                formatter: {
                                    type: 'number',
                                    maximumFractionDigits: 0,
                                },
                            };

                            const options = {
                                ...defaults,
                                ...(pluginOptions || {}),
                                font: {
                                    ...defaults.font,
                                    ...(pluginOptions?.font || {}),
                                },
                            };

                            ctx.save();
                            ctx.font = `${options.font.weight} ${options.font.size}px ${options.font.family}`;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = options.color;

                            chart.data.datasets.forEach((dataset, datasetIndex) => {
                                const meta = chart.getDatasetMeta(datasetIndex);

                                if (!meta || meta.hidden || meta?.visible === false) {
                                    return;
                                }

                                meta.data.forEach((element, index) => {
                                    const value = dataset.data?.[index];

                                    if (!options.showZero && (!value || value === 0)) {
                                        return;
                                    }

                                    let displayValue = value;

                                    if (options.formatter?.type === 'currency') {
                                        const suffix = options.formatter.suffix || '';
                                        try {
                                            displayValue = new Intl.NumberFormat(options.locale, {
                                                maximumFractionDigits: options.formatter.maximumFractionDigits || 0,
                                            }).format(value) + suffix;
                                        } catch (error) {
                                            displayValue = `${value}`;
                                        }
                                    } else if (options.formatter?.type === 'number') {
                                        try {
                                            displayValue = new Intl.NumberFormat(options.locale, {
                                                maximumFractionDigits: options.formatter.maximumFractionDigits || 0,
                                            }).format(value);
                                        } catch (error) {
                                            displayValue = `${value}`;
                                        }
                                    }

                                    const x = element?.x ?? 0;
                                    let y = (element?.y ?? 0) - options.padding;

                                    if (y < chartArea.top) {
                                        y = chartArea.top + options.padding;
                                    }

                                    ctx.fillText(displayValue, x, y);
                                });
                            });

                            ctx.restore();
                        },
                    };

                    if (Chart?.register) {
                        Chart.register(BarValueLabelsPlugin);
                    } else if (Chart?.plugins?.register) {
                        Chart.plugins.register(BarValueLabelsPlugin);
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', attemptRegister);
                } else {
                    attemptRegister();
                }

                window.addEventListener('livewire:navigated', () => {
                    setTimeout(attemptRegister, 100);
                });
            })();

            const triggerResize = () => window.dispatchEvent(new Event('resize'));

            document.addEventListener('livewire:navigated', () => {
                requestAnimationFrame(triggerResize);
            });

            window.addEventListener('orientationchange', () => {
                setTimeout(triggerResize, 120);
            });
        </script>
    @endpush
@endonce
