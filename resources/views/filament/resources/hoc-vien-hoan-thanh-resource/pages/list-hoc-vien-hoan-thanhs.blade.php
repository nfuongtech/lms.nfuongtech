<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                .fi-ta-header .fi-ta-search {
                    display: none !important;
                }

                .fi-ta-filter-indicators > span:first-child {
                    display: none;
                }

                .fi-ta-filter-indicators::before {
                    content: 'Đang áp dụng lọc';
                    margin-right: 0.5rem;
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: rgb(55 65 81);
                }
            </style>
        @endonce

        @php($pageHeading = trim($this->getHeading() ?? $this->getTitle() ?? ''))
        @php($headerActions = method_exists($this, 'getCachedHeaderActions') ? $this->getCachedHeaderActions() : [])
        @php($filters = $this->filterState)

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                @if($pageHeading !== '')
                    <div class="text-2xl font-semibold text-gray-900">{{ $pageHeading }}</div>
                @endif
                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                    <span>Năm: <strong>{{ $filters['year'] }}</strong></span>
                    <span>Tháng: <strong>{{ $filters['month'] ?? '-' }}</strong></span>
                    @if(! empty($filters['week']))
                        <span>Tuần: <strong>{{ $filters['week'] }}</strong></span>
                    @endif
                    @if(! empty($filters['from_date']))
                        <span>Từ ngày: <strong>{{ \Illuminate\Support\Carbon::parse($filters['from_date'])->format('d/m/Y') }}</strong></span>
                    @endif
                    @if(! empty($filters['to_date']))
                        <span>Đến ngày: <strong>{{ \Illuminate\Support\Carbon::parse($filters['to_date'])->format('d/m/Y') }}</strong></span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if(property_exists($this, 'tableSearch'))
                    <label class="relative flex items-center">
                        <span class="pointer-events-none absolute left-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            wire:model.debounce.500ms="tableSearch"
                            placeholder="Tìm kiếm học viên..."
                            class="fi-input block h-9 w-56 rounded-lg border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        />
                    </label>
                @endif

                @if(! empty($headerActions))
                    <div class="flex flex-wrap gap-2">
                        @foreach($headerActions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @php($selectedCourse = $this->filterState['course_id'] ?? null)
        @php($totals = $this->summaryTotals)

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                    <p class="text-xs text-gray-500">Nhấn vào hàng trong bảng bên dưới để xem chi tiết danh sách học viên hoàn thành theo từng khóa học.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold">TT</th>
                            <th class="px-3 py-2 font-semibold">Mã khóa</th>
                            <th class="px-3 py-2 font-semibold">Tên khóa học</th>
                            <th class="px-3 py-2 font-semibold">Trạng thái</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng số giờ</th>
                            <th class="px-3 py-2 font-semibold">Giảng viên</th>
                            <th class="px-3 py-2 font-semibold">Thời gian đào tạo</th>
                            <th class="px-3 py-2 text-center font-semibold">Số lượng HV</th>
                            <th class="px-3 py-2 text-center font-semibold">Hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Không hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng thu</th>
                            <th class="px-3 py-2 font-semibold">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->summaryRows as $row)
                            <tr
                                wire:key="summary-{{ $row['id'] }}"
                                wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                class="cursor-pointer transition hover:bg-primary-50 {{ $selectedCourse === $row['id'] ? 'bg-primary-50' : 'bg-white' }}"
                            >
                                <td class="px-3 py-2 text-center font-medium text-gray-900">{{ $row['index'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $row['ma_khoa'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['ten_khoa'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $this->statusBadgeClass($row['trang_thai'] ?? null) }} whitespace-nowrap">
                                        {{ $row['trang_thai'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_gio'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-pre-line">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ number_format($row['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600 font-semibold">{{ number_format($row['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600 font-semibold">{{ number_format($row['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_thu'] > 0 ? number_format($row['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $row['ghi_chu'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500">Chưa có khóa học phù hợp với bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($this->summaryRows->isNotEmpty())
                        <tfoot class="bg-slate-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right">Tổng cộng</td>
                                <td class="px-3 py-2 text-center">{{ number_format($totals['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600">{{ number_format($totals['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600">{{ number_format($totals['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center">{{ $totals['tong_thu'] > 0 ? number_format($totals['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-lg font-semibold text-gray-900">Danh sách học viên hoàn thành</h2>
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
