{{-- resources/views/filament/widgets/partials/chart-plugin.blade.php --}}
{{-- Include file này vào layout hoặc dashboard --}}

@once
@push('scripts')
<script>
// Đợi Chart.js được load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js chưa được load!');
        return;
    }

    console.log('Chart.js version:', Chart.version);

    // Plugin Bar Value Labels
    const BarValueLabelsPlugin = {
        id: 'barValueLabels',
        
        afterDatasetsDraw(chart, args, pluginOptions) {
            const { ctx, chartArea, scales } = chart;
            
            if (!chartArea || pluginOptions.display === false) {
                return;
            }

            const options = {
                padding: 10,
                color: '#0f172a',
                font: {
                    size: 11,
                    weight: '600',
                    family: 'system-ui, -apple-system, sans-serif'
                },
                align: 'center',
                verticalAlign: 'bottom',
                anchor: 'end',
                showZero: true,
                locale: 'vi-VN',
                formatter: {
                    type: 'number',
                    maximumFractionDigits: 0
                },
                ...pluginOptions
            };

            ctx.save();
            ctx.font = `${options.font.weight} ${options.font.size}px ${options.font.family}`;
            ctx.textAlign = options.align;
            ctx.textBaseline = 'middle';
            ctx.fillStyle = options.color;

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                
                if (!meta || !meta.visible || meta.hidden) {
                    return;
                }

                meta.data.forEach((element, index) => {
                    const value = dataset.data[index];
                    
                    // Skip if value is 0 and showZero is false
                    if (!options.showZero && (!value || value === 0)) {
                        return;
                    }

                    // Format value
                    let displayValue = value;
                    if (options.formatter && options.formatter.type === 'number') {
                        try {
                            displayValue = new Intl.NumberFormat(options.locale, {
                                maximumFractionDigits: options.formatter.maximumFractionDigits || 0
                            }).format(value);
                        } catch (e) {
                            displayValue = value.toString();
                        }
                    }

                    // Calculate position
                    const x = element.x;
                    let y = element.y - options.padding;

                    // Ensure label is within chart area
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
        console.log('✓ Bar Value Labels Plugin registered (Chart.js 3+)');
    } else if (Chart.plugins) {
        Chart.plugins.register(BarValueLabelsPlugin);
        console.log('✓ Bar Value Labels Plugin registered (Chart.js 2)');
    } else {
        console.error('Cannot register plugin - Chart.js registry not found');
    }
});
</script>
@endpush
@endonce
