{{-- resources/views/filament/widgets/partials/dashboard-chart-script.blade.php --}}
@once
    @push('styles')
        <style>
            /* Đảm bảo canvas full-width trong card Filament */
            .fi-wi-chart { 
                width: 100% !important; 
                min-width: 0; 
            }
            .fi-wi-chart canvas { 
                width: 100% !important; 
                max-width: 100% !important; 
                display: block; 
            }
            .fi-widget canvas { 
                width: 100% !important; 
                max-width: 100% !important; 
                display: block; 
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // ĐĂNG KÝ PLUGIN BAR VALUE LABELS CHỈ 1 LẦN
            (function() {
                function registerBarValueLabelsPlugin() {
                    if (typeof Chart === 'undefined') {
                        console.warn('Chart.js chưa load, đợi 300ms...');
                        setTimeout(registerBarValueLabelsPlugin, 300);
                        return;
                    }

                    // Kiểm tra xem plugin đã được đăng ký chưa
                    if (Chart.registry && Chart.registry.plugins.get('barValueLabels')) {
                        console.log('✓ Plugin barValueLabels đã tồn tại');
                        return;
                    }

                    const BarValueLabelsPlugin = {
                        id: 'barValueLabels',
                        
                        afterDatasetsDraw(chart, args, pluginOptions) {
                            const { ctx, chartArea } = chart;
                            
                            if (!chartArea || pluginOptions.display === false) {
                                return;
                            }

                            const defaults = {
                                padding: 10,
                                color: '#0f172a',
                                font: {
                                    size: 11,
                                    weight: '600',
                                    family: 'system-ui, -apple-system, sans-serif'
                                },
                                showZero: true,
                                locale: 'vi-VN',
                                formatter: {
                                    type: 'number',
                                    maximumFractionDigits: 0
                                }
                            };

                            const options = { ...defaults, ...pluginOptions };
                            if (options.font) {
                                options.font = { ...defaults.font, ...options.font };
                            }

                            ctx.save();
                            ctx.font = `${options.font.weight} ${options.font.size}px ${options.font.family}`;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = options.color;

                            chart.data.datasets.forEach((dataset, datasetIndex) => {
                                const meta = chart.getDatasetMeta(datasetIndex);
                                
                                if (!meta || !meta.visible || meta.hidden) {
                                    return;
                                }

                                meta.data.forEach((element, index) => {
                                    const value = dataset.data[index];
                                    
                                    if (!options.showZero && (!value || value === 0)) {
                                        return;
                                    }

                                    let displayValue = value;
                                    
                                    // Format number
                                    if (options.formatter) {
                                        try {
                                            if (options.formatter.type === 'currency') {
                                                // Format currency
                                                const suffix = options.formatter.suffix || '';
                                                displayValue = new Intl.NumberFormat(options.locale, {
                                                    maximumFractionDigits: options.formatter.maximumFractionDigits || 0
                                                }).format(value) + suffix;
                                            } else if (options.formatter.type === 'number') {
                                                // Format number
                                                displayValue = new Intl.NumberFormat(options.locale, {
                                                    maximumFractionDigits: options.formatter.maximumFractionDigits || 0
                                                }).format(value);
                                            }
                                        } catch (e) {
                                            displayValue = value.toString();
                                        }
                                    }

                                    // Calculate position
                                    const x = element.x;
                                    let y = element.y - options.padding;

                                    // Ensure within chart area
                                    if (y < chartArea.top) {
                                        y = chartArea.top + options.padding;
                                    }

                                    // Draw text
                                    ctx.fillText(displayValue, x, y);
                                });
                            });

                            ctx.restore();
                        }
                    };

                    // Register plugin
                    if (Chart.registry) {
                        Chart.register(BarValueLabelsPlugin);
                        console.log('✓ Bar Value Labels Plugin đã đăng ký thành công');
                    } else if (Chart.plugins) {
                        Chart.plugins.register(BarValueLabelsPlugin);
                        console.log('✓ Bar Value Labels Plugin đã đăng ký thành công (legacy)');
                    }
                }

                // Khởi tạo khi document ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', registerBarValueLabelsPlugin);
                } else {
                    registerBarValueLabelsPlugin();
                }
            })();

            // Trigger resize cho charts
            const triggerResize = () => {
                window.dispatchEvent(new Event('resize'));
            };
            
            document.addEventListener('livewire:navigated', () => {
                requestAnimationFrame(triggerResize);
            });
            
            window.addEventListener('orientationchange', () => {
                setTimeout(triggerResize, 50);
            });
        </script>
    @endpush
@endonce
