<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
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

                .hvht-action-bar {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: flex-end;
                    gap: 0.5rem;
                }

                .hvht-sticky-tt,
                .hvht-sticky-ms,
                .hvht-sticky-name {
                    position: sticky;
                    min-width: var(--hvht-width);
                    width: var(--hvht-width);
                    box-sizing: border-box;
                }

                .hvht-sticky-tt {
                    --hvht-width: 3.5rem;
                    left: 0;
                }

                .hvht-sticky-ms {
                    --hvht-width: 7rem;
                    left: 3.5rem;
                }

                .hvht-sticky-name {
                    --hvht-width: 16rem;
                    left: 10.5rem;
                }

                .hvht-table-sticky-tt,
                .hvht-table-sticky-ms,
                .hvht-table-sticky-name {
                    position: sticky;
                    min-width: var(--hvht-width);
                    width: var(--hvht-width);
                    background-color: inherit;
                    box-sizing: border-box;
                }

                .hvht-table-sticky-tt {
                    --hvht-width: 3.5rem;
                    left: 0;
                }

                .hvht-table-sticky-ms {
                    --hvht-width: 7rem;
                    left: 3.5rem;
                }

                .hvht-table-sticky-name {
                    --hvht-width: 16rem;
                    left: 10.5rem;
                }

                thead .hvht-sticky-tt,
                thead .hvht-sticky-ms,
                thead .hvht-sticky-name {
                    top: 0;
                    z-index: 40;
                    background-color: rgb(243 244 246);
                }

                tbody .hvht-sticky-tt,
                tbody .hvht-sticky-ms,
                tbody .hvht-sticky-name {
                    z-index: 30;
                    background-color: inherit;
                }

                .fi-ta-table thead .hvht-table-sticky-tt,
                .fi-ta-table thead .hvht-table-sticky-ms,
                .fi-ta-table thead .hvht-table-sticky-name {
                    top: 0;
                    z-index: 45;
                    background-color: rgb(243 244 246);
                }

                .fi-ta-table tbody .hvht-table-sticky-tt,
                .fi-ta-table tbody .hvht-table-sticky-ms,
                .fi-ta-table tbody .hvht-table-sticky-name {
                    z-index: 35;
                }
            </style>
        @endonce

        @php($headerActions = method_exists($this, 'getCachedHeaderActions') ? $this->getCachedHeaderActions() : [])

        @if(! empty($headerActions))
            <div class="hvht-action-bar">
                @foreach($headerActions as $action)
                    {{ $action }}
                @endforeach
            </div>
        @endif

        @php($selectedCourse = $this->filterState['course_id'] ?? null)
        @php($totals = $this->summaryTotals)

        <div class="bg-white shadow rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold hvht-sticky-tt">TT</th>
                            <th class="px-3 py-2 font-semibold hvht-sticky-ms">Mã khóa</th>
                            <th class="px-3 py-2 font-semibold hvht-sticky-name">Tên khóa học</th>
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
                                <td class="px-3 py-2 text-center font-medium text-gray-900 hvht-sticky-tt">{{ $row['index'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 hvht-sticky-ms">{{ $row['ma_khoa'] }}</td>
                                <td class="px-3 py-2 text-gray-700 hvht-sticky-name">{{ $row['ten_khoa'] }}</td>
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
