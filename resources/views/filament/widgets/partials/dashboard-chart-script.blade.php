@once
    @push('scripts')
        <script>
            (function () {
                if (typeof window.Chart === 'undefined') return;

                // Helper clone & normalizer chung cho các ChartWidget khác (không động chạm Alpine)
                const Chart = window.Chart;

                const deepClone = (value) => {
                    if (Array.isArray(value)) return value.map(deepClone);
                    if (value && typeof value === 'object') {
                        return Object.keys(value).reduce((carry, key) => {
                            carry[key] = deepClone(value[key]);
                            return carry;
                        }, {});
                    }
                    return value;
                };

                const normalizeData = (data) => {
                    const d = deepClone(data || {});
                    d.datasets = (d.datasets || []).map((ds) => {
                        // Mặc định bo góc & độ dày cột thân thiện
                        if (typeof ds.borderRadius === 'undefined') ds.borderRadius = 6;
                        if (typeof ds.maxBarThickness === 'undefined') ds.maxBarThickness = 44;
                        return ds;
                    });
                    return d;
                };

                const normalizeOptions = (options) => {
                    const base = {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true } },
                            tooltip: { mode: 'index', intersect: false },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                    };
                    return Object.assign({}, base, options || {});
                };

                // Không đăng ký Alpine.data('trainingCostChart') để tránh xung đột
                // Các widget khác có thể dùng helpers này khi cần:
                window.__filamentChartHelpers = {
                    normalizeData,
                    normalizeOptions,
                };
            })();
        </script>
    @endpush
@endonce
