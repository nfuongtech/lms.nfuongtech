<x-filament::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Năm</label>
                <select wire:model.live="selectedNam" class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Chọn năm --</option>
                    @foreach($availableNams as $nam)
                        <option value="{{ $nam }}">{{ $nam }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tuần</label>
                <select wire:model.live="selectedTuan" class="fi-input w-40 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Tất cả --</option>
                    @foreach($availableWeeks as $tuan)
                        <option value="{{ $tuan }}">Tuần {{ $tuan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
                <input type="date" wire:model.live="fromDate" class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
                <input type="date" wire:model.live="toDate" class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div class="min-w-[18rem]">
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model.live="selectedKhoaHoc" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Tất cả khóa học --</option>
                    @foreach($availableCourses as $course)
                        <option value="{{ $course->id }}">{{ $course->ma_khoa_hoc }} - {{ $course->chuongTrinh->ten_chuong_trinh ?? $course->ten_khoa_hoc }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 space-y-4">
            <h3 class="text-lg font-semibold">Khóa học có học viên không hoàn thành</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="w-14 px-3 py-2 border-b">TT</th>
                            <th class="w-32 px-3 py-2 border-b">Mã khóa</th>
                            <th class="px-3 py-2 border-b">Tên khóa học</th>
                            <th class="w-24 px-3 py-2 border-b text-center">Số buổi</th>
                            <th class="w-32 px-3 py-2 border-b">Tuần</th>
                            <th class="w-48 px-3 py-2 border-b">Ngày đào tạo</th>
                            <th class="w-56 px-3 py-2 border-b">Giảng viên</th>
                            <th class="w-40 px-3 py-2 border-b text-center">SL không hoàn thành</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($courseRows as $index => $row)
                            <tr>
                                <td class="px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $row['ma_khoa_hoc'] }}</td>
                                <td class="px-3 py-2">{{ $row['ten_khoa_hoc'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['so_buoi'] }}</td>
                                <td class="px-3 py-2">{{ $row['tuan'] }}</td>
                                <td class="px-3 py-2">{{ $row['ngay_dao_tao'] }}</td>
                                <td class="px-3 py-2">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['so_luong_khong_hoan_thanh'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-gray-500">Không có dữ liệu phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 space-y-4">
            <h3 class="text-lg font-semibold">Danh sách học viên không hoàn thành</h3>

            <div class="overflow-x-auto relative">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="w-14 px-3 py-2 border-b sticky left-0 z-30 bg-gray-100">TT</th>
                            <th class="w-32 px-3 py-2 border-b sticky left-14 z-30 bg-gray-100" style="left:3.5rem;">Mã số</th>
                            <th class="px-3 py-2 border-b sticky z-30 bg-gray-100" style="left:11.5rem; min-width:14rem;">Họ &amp; Tên</th>
                            <th class="w-40 px-3 py-2 border-b">Ngày sinh</th>
                            <th class="w-28 px-3 py-2 border-b">Giới tính</th>
                            <th class="w-40 px-3 py-2 border-b">Chức vụ</th>
                            <th class="w-48 px-3 py-2 border-b">Đơn vị</th>
                            <th class="w-56 px-3 py-2 border-b">Thời gian đào tạo</th>
                            <th class="w-56 px-3 py-2 border-b">Chuyên cần &amp; Điểm</th>
                            <th class="w-40 px-3 py-2 border-b">Lý do không hoàn thành</th>
                            <th class="w-32 px-3 py-2 border-b text-center">ĐTB</th>
                            <th class="w-40 px-3 py-2 border-b text-center">Có thể ghi danh lại</th>
                            <th class="px-3 py-2 border-b" style="min-width:18rem;">Đánh giá rèn luyện</th>
                            <th class="w-40 px-3 py-2 border-b text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($hocVienRows as $index => $row)
                            <tr class="align-top">
                                <td class="px-3 py-3 text-center sticky left-0 bg-white z-20 border-r border-gray-200">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap sticky left-14 bg-white z-20 border-r border-gray-200" style="left:3.5rem;">{{ $row['ma_so'] }}</td>
                                <td class="px-3 py-3 break-words sticky bg-white z-20 border-r border-gray-200" style="left:11.5rem; min-width:14rem;">{{ $row['ho_ten'] }}</td>
                                <td class="px-3 py-3">{{ $row['ngay_sinh'] ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $row['gioi_tinh'] }}</td>
                                <td class="px-3 py-3">{{ $row['chuc_vu'] }}</td>
                                <td class="px-3 py-3">{{ $row['don_vi'] }}</td>
                                <td class="px-3 py-3">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-3"><pre class="whitespace-pre-wrap text-sm">{{ $row['chuyen_can_diem'] }}</pre></td>
                                <td class="px-3 py-3">{{ $row['ly_do'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $row['diem_trung_binh'] !== null ? number_format((float) $row['diem_trung_binh'], 2) : '—' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-1 inline-block rounded text-xs font-semibold {{ $row['co_the_ghi_danh_lai'] ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $row['co_the_ghi_danh_lai'] ? 'Có' : 'Không' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">{{ $row['danh_gia_ren_luyen'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-center space-y-2">
                                    <div class="flex flex-col gap-2">
                                        <button type="button" wire:click="openEditModal({{ $row['id'] }})" class="rounded px-3 py-1 border border-gray-300 text-sm bg-white hover:bg-gray-100">Sửa</button>
                                        <button type="button" wire:click="confirmApprove({{ $row['id'] }})" class="rounded px-3 py-1 text-sm font-medium text-green-900 border border-green-200 shadow-sm hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2" style="background-color:#CCFFD8;">Chuyển hoàn thành</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-3 py-6 text-center text-gray-500">Không có học viên nào trong danh sách.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showEditModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold">Cập nhật lý do</h3>
                <form wire:submit.prevent="saveEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lý do không hoàn thành</label>
                        <textarea wire:model.defer="editForm.ly_do_khong_hoan_thanh" rows="4" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tối đa 500 ký tự"></textarea>
                        @error('editForm.ly_do_khong_hoan_thanh') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="editForm.co_the_ghi_danh_lai" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label class="text-sm text-gray-700">Đề xuất ghi danh lại</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="$set('showEditModal', false)" class="rounded px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">Hủy</button>
                        <button type="submit" class="rounded px-4 py-2 bg-primary-600 text-white hover:bg-primary-700">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showApproveModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-semibold text-green-700">Chuyển học viên sang hoàn thành</h3>
                <p class="text-sm text-gray-600">Hành động này sẽ chuyển học viên sang danh sách hoàn thành và cập nhật kết quả tương ứng.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showApproveModal', false)" class="rounded px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">Hủy</button>
                    <button type="button" wire:click="approveRecord" class="rounded px-4 py-2 bg-green-600 text-white hover:bg-green-700">Đồng ý</button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
