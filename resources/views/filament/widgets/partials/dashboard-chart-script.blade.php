@once
    @push('scripts')
        <script>
            (function () {
                if (typeof window.Chart === 'undefined') return;

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
            })();
        </script>
    @endpush
@endonce
