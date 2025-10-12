<x-filament::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Năm</label>
                <select
                    wire:model.live="selectedNam"
                    class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">-- Chọn năm --</option>
                    @foreach($availableNams as $nam)
                        <option value="{{ $nam }}">{{ $nam }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tuần</label>
                <select
                    wire:model.live="selectedTuan"
                    class="fi-input w-40 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">-- Tất cả --</option>
                    @foreach($availableWeeks as $tuan)
                        <option value="{{ $tuan }}">Tuần {{ $tuan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
                <input
                    type="date"
                    wire:model.live="fromDate"
                    class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
                <input
                    type="date"
                    wire:model.live="toDate"
                    class="fi-input w-44 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
            </div>

            <div class="min-w-[18rem]">
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select
                    wire:model.live="selectedKhoaHoc"
                    class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">-- Tất cả khóa học --</option>
                    @foreach($availableCourses as $course)
                        <option value="{{ $course->id }}">{{ $course->ma_khoa_hoc }} - {{ $course->chuongTrinh->ten_chuong_trinh ?? $course->ten_khoa_hoc }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h3 class="text-lg font-semibold">Khóa học hoàn thành theo bộ lọc</h3>
                <button
                    wire:click="exportSummary"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-green-900 border border-green-200 shadow-sm hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2"
                    style="background-color:#CCFFD8;"
                >
                    Xuất Excel
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="w-14 px-3 py-2 border-b">TT</th>
                            <th class="w-32 px-3 py-2 border-b">Mã khóa</th>
                            <th class="px-3 py-2 border-b">Tên khóa học</th>
                            <th class="w-32 px-3 py-2 border-b text-right">Tổng số giờ</th>
                            <th class="w-24 px-3 py-2 border-b text-center">Số buổi</th>
                            <th class="w-32 px-3 py-2 border-b">Tuần</th>
                            <th class="w-48 px-3 py-2 border-b">Ngày đào tạo</th>
                            <th class="w-56 px-3 py-2 border-b">Giảng viên</th>
                            <th class="w-40 px-3 py-2 border-b text-center">SL học viên đăng ký</th>
                            <th class="w-40 px-3 py-2 border-b text-center">SL học viên hoàn thành</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($courseRows as $index => $row)
                            <tr>
                                <td class="px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $row['ma_khoa_hoc'] }}</td>
                                <td class="px-3 py-2">{{ $row['ten_khoa_hoc'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['tong_so_gio'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['so_buoi'] }}</td>
                                <td class="px-3 py-2">{{ $row['tuan'] }}</td>
                                <td class="px-3 py-2">{{ $row['ngay_dao_tao'] }}</td>
                                <td class="px-3 py-2">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['so_luong_hv'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['so_luong_hoan_thanh'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-6 text-center text-gray-500">Không có khóa học phù hợp bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 space-y-4">
            <h3 class="text-lg font-semibold">Danh sách học viên hoàn thành</h3>

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
                            <th class="w-40 px-3 py-2 border-b">Hoàn thành khóa học</th>
                            <th class="w-32 px-3 py-2 border-b text-right">Số giờ học</th>
                            <th class="w-56 px-3 py-2 border-b">Thời gian</th>
                            <th class="w-56 px-3 py-2 border-b">Chuyên cần &amp; Điểm</th>
                            <th class="w-28 px-3 py-2 border-b text-center">ĐTB</th>
                            <th class="px-3 py-2 border-b" style="min-width:18rem;">Đánh giá rèn luyện</th>
                            <th class="w-32 px-3 py-2 border-b text-center">Kết quả</th>
                            <th class="w-48 px-3 py-2 border-b text-center">Hành động</th>
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
                                <td class="px-3 py-3">{{ $row['chuc_vu'] ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $row['don_vi'] ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $row['ngay_hoan_thanh'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-right">{{ $row['so_gio_hoc'] }}</td>
                                <td class="px-3 py-3">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-3"><pre class="whitespace-pre-wrap text-sm">{{ $row['chuyen_can_diem'] }}</pre></td>
                                <td class="px-3 py-3 text-center">{{ $row['diem_trung_binh'] !== null ? number_format((float) $row['diem_trung_binh'], 2) : '—' }}</td>
                                <td class="px-3 py-3">{{ $row['danh_gia_ren_luyen'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-1 inline-block rounded text-xs font-semibold {{ ($row['ket_qua'] ?? '') === 'hoan_thanh' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ ($row['ket_qua'] ?? '') === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành' }}
                                    </span>
                                    @if($row['da_duyet'])
                                        <div class="text-xs text-gray-500 mt-1">Duyệt lúc {{ $row['ngay_duyet'] }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center space-y-2">
                                    <div class="flex flex-col gap-2">
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $row['id'] }})"
                                            class="rounded px-3 py-1 border border-gray-300 text-sm bg-white hover:bg-gray-100"
                                        >
                                            Sửa
                                        </button>

                                        @unless($row['da_duyet'])
                                            <button
                                                type="button"
                                                wire:click="approveRecord({{ $row['id'] }})"
                                                class="rounded px-3 py-1 text-sm font-medium text-green-900 border border-green-200 shadow-sm hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2"
                                                style="background-color:#CCFFD8;"
                                            >
                                                Duyệt
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="openSupplementModal({{ $row['id'] }})"
                                                class="rounded px-3 py-1 text-sm border border-primary-300 text-primary-700 bg-primary-50 hover:bg-primary-100"
                                            >
                                                Bổ sung
                                            </button>
                                        @endunless

                                        <button
                                            type="button"
                                            wire:click="confirmReject({{ $row['id'] }})"
                                            class="rounded px-3 py-1 text-sm border border-red-300 text-red-700 bg-red-50 hover:bg-red-100"
                                        >
                                            Không duyệt
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-3 py-6 text-center text-gray-500">Không có học viên hoàn thành phù hợp bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showEditModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Cập nhật đánh giá</h3>
                <form wire:submit.prevent="saveEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đánh giá rèn luyện</label>
                        <textarea
                            wire:model.defer="editForm.danh_gia_ren_luyen"
                            rows="4"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Nhập tối đa 500 ký tự"
                        ></textarea>
                        @error('editForm.danh_gia_ren_luyen') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                        <textarea
                            wire:model.defer="editForm.ghi_chu"
                            rows="3"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Nhập ghi chú nếu cần"
                        ></textarea>
                        @error('editForm.ghi_chu') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="rounded px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">Hủy</button>
                        <button type="submit" class="rounded px-4 py-2 bg-primary-600 text-white hover:bg-primary-700">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showSupplementModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 space-y-4">
                <h3 class="text-lg font-semibold">Bổ sung thông tin chứng chỉ</h3>
                <form wire:submit.prevent="saveSupplement" class="space-y-4" enctype="multipart/form-data">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Học phí (VND)</label>
                        <input type="number" step="0.01" wire:model.defer="supplementForm.chi_phi_dao_tao" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('supplementForm.chi_phi_dao_tao') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="supplementForm.chung_chi_da_cap" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label class="text-sm text-gray-700">Đã cấp chứng chỉ</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Link chứng nhận</label>
                        <input type="text" wire:model.defer="supplementForm.chung_chi_link" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://">
                        @error('supplementForm.chung_chi_link') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tải file chứng nhận (PDF)</label>
                        <input type="file" wire:model="supplementForm.chung_chi_tap_tin" accept="application/pdf" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('supplementForm.chung_chi_tap_tin') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        @if(isset($supplementForm['chung_chi_tap_tin']) && method_exists($supplementForm['chung_chi_tap_tin'], 'getClientOriginalName'))
                            <p class="text-xs text-gray-500 mt-1">Tệp: {{ $supplementForm['chung_chi_tap_tin']->getClientOriginalName() }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Số chứng nhận</label>
                        <input type="text" wire:model.defer="supplementForm.so_chung_nhan" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('supplementForm.so_chung_nhan') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Thời gian hết hiệu lực</label>
                        <input type="date" wire:model.defer="supplementForm.chung_chi_het_han" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('supplementForm.chung_chi_het_han') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="$set('showSupplementModal', false)" class="rounded px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">Hủy</button>
                        <button type="submit" class="rounded px-4 py-2 bg-primary-600 text-white hover:bg-primary-700">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showRejectModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold text-red-700">Không duyệt kết quả</h3>
                <p class="text-sm text-gray-600">Không duyệt sẽ chuyển học viên qua trạng thái Không hoàn thành.</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lý do (nếu có)</label>
                    <textarea wire:model.defer="rejectReason" rows="3" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tối đa 500 ký tự"></textarea>
                    @error('rejectReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showRejectModal', false)" class="rounded px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">Hủy</button>
                    <button type="button" wire:click="rejectRecord" class="rounded px-4 py-2 bg-red-600 text-white hover:bg-red-700">Không duyệt</button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
