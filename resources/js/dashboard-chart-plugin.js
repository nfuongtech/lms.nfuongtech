// resources/js/dashboard-chart-plugin.js
// Plugin Chart.js để hiển thị giá trị trên các cột (bars)

(function() {
    'use strict';

    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded. Bar value labels plugin will not work.');
        return;
    }

    /**
     * Plugin hiển thị giá trị trên các cột của biểu đồ Bar
     */
    const BarValueLabelsPlugin = {
        id: 'barValueLabels',

        afterDatasetsDraw(chart, args, options) {
            const { ctx, data, scales } = chart;
            const config = options || {};

            // Kiểm tra nếu plugin bị tắt
            if (config.display === false) {
                return;
            }

            // Cấu hình mặc định
            const defaults = {
                padding: 8,
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
                    locale: 'vi-VN',
                    maximumFractionDigits: 0
                }
            };

            const settings = { ...defaults, ...config };
            
            if (settings.font) {
                settings.font = { ...defaults.font, ...settings.font };
            }

            ctx.save();
            ctx.font = `${settings.font.weight} ${settings.font.size}px ${settings.font.family}`;
            ctx.textAlign = settings.align;
            ctx.textBaseline = 'middle';
            ctx.fillStyle = settings.color;

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                
                if (!meta.visible) {
                    return;
                }

                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index];
                    
                    // Bỏ qua nếu không hiển thị giá trị 0
                    if (!settings.showZero && (value === 0 || value === null || value === undefined)) {
                        return;
                    }

                    // Format giá trị
                    let displayValue = value;
                    
                    if (settings.formatter) {
                        if (settings.formatter.type === 'currency') {
                            displayValue = this.formatCurrency(value, settings.formatter);
                        } else if (settings.formatter.type === 'number') {
                            displayValue = this.formatNumber(value, settings.formatter);
                        }
                    }

                    // Tính vị trí hiển thị
                    const x = bar.x;
                    let y = bar.y;

                    // Điều chỉnh vị trí theo anchor và verticalAlign
                    if (settings.verticalAlign === 'bottom' && settings.anchor === 'end') {
                        y = bar.y - settings.padding;
                    } else if (settings.verticalAlign === 'top' && settings.anchor === 'start') {
                        y = bar.base + settings.padding;
                    } else if (settings.verticalAlign === 'center') {
                        y = (bar.y + bar.base) / 2;
                    }

                    // Vẽ text
                    ctx.fillText(displayValue, x, y);
                });
            });

            ctx.restore();
        },

        formatNumber(value, formatter) {
            if (value === null || value === undefined || isNaN(value)) {
                return '0';
            }

            const locale = formatter.locale || 'vi-VN';
            const options = {
                maximumFractionDigits: formatter.maximumFractionDigits ?? 0,
                minimumFractionDigits: formatter.minimumFractionDigits ?? 0
            };

            return new Intl.NumberFormat(locale, options).format(value);
        },

        formatCurrency(value, formatter) {
            if (value === null || value === undefined || isNaN(value)) {
                return '0';
            }

            const locale = formatter.locale || 'vi-VN';
            const suffix = formatter.suffix || '';
            const divisor = formatter.divisor || 1;
            
            const adjustedValue = value / divisor;
            
            const options = {
                maximumFractionDigits: formatter.maximumFractionDigits ?? 0,
                minimumFractionDigits: formatter.minimumFractionDigits ?? 0
            };

            let formatted = new Intl.NumberFormat(locale, options).format(adjustedValue);
            
            if (suffix) {
                formatted += suffix;
            }

            return formatted;
        }
    };

    // Đăng ký plugin
    if (Chart.registry) {
        Chart.register(BarValueLabelsPlugin);
    } else {
        // Fallback cho phiên bản cũ
        Chart.plugins.register(BarValueLabelsPlugin);
    }

    console.log('✓ Bar Value Labels Plugin registered successfully');

})();

/**
 * Helper function để format tooltip callbacks
 */
window.formatTooltipCallback = function(context, options) {
    options = options || {};
    const locale = options.locale || 'vi-VN';
    const suffix = options.suffix || '';
    
    let label = context.dataset.label || '';
    
    if (label) {
        label += ': ';
    }
    
    if (context.parsed.y !== null) {
        const formatted = new Intl.NumberFormat(locale, {
            maximumFractionDigits: options.maximumFractionDigits ?? 0
        }).format(context.parsed.y);
        
        label += formatted + suffix;
    }
    
    return label;
};

/**
 * Helper function để tính tổng của stack trong tooltip footer
 */
window.formatStackedTooltipFooter = function(tooltipItems, stackName, label, options) {
    options = options || {};
    const locale = options.locale || 'vi-VN';
    const suffix = options.suffix || ' HV';
    
    let sum = 0;
    
    tooltipItems.forEach(item => {
        const dataset = item.dataset;
        if (dataset.stack === stackName) {
            sum += item.parsed.y || 0;
        }
    });
    
    if (sum === 0) {
        return '';
    }
    
    const formatted = new Intl.NumberFormat(locale, {
        maximumFractionDigits: 0
    }).format(sum);
    
    return (label || 'Tổng') + ': ' + formatted + suffix;
};
