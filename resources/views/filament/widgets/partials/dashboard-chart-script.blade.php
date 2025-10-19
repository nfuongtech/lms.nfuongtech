@once
    @push('styles')
        <style>
            /* Bắt canvas + wrapper luôn full-width trong thẻ card của Filament */
            .fi-wi-chart { width: 100% !important; min-width: 0; }
            .fi-wi-chart canvas { width: 100% !important; max-width: 100% !important; display: block; }
            .fi-widget canvas { width: 100% !important; max-width: 100% !important; display: block; }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Ép Chart.js của Filament cập nhật kích thước khi điều hướng/đổi hướng thiết bị
            const triggerResize = () => window.dispatchEvent(new Event('resize'));
            document.addEventListener('livewire:navigated', () => requestAnimationFrame(triggerResize));
            window.addEventListener('orientationchange', () => setTimeout(triggerResize, 50));
        </script>
    @endpush
@endonce
