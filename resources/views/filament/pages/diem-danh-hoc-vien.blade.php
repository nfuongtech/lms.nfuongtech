{{-- resources/views/filament/pages/diem-danh-hoc-vien.blade.php --}}
<x-filament::page>
    <div class="space-y-6">
        {{-- BỘ LỌC --}}
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Năm</label>
                <select
                    wire:model.live="selectedNam"
                    class="fi-input w-48 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">-- Chọn năm --</option>
                    @foreach($availableNams as $nam)
                        <option value="{{ $nam }}">{{ $nam }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedNam)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tuần</label>
                    <select
                        wire:model.live="selectedTuan"
                        class="fi-input w-40 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">-- Chọn tuần --</option>
                        @foreach($availableWeeks as $tuan)
                            <option value="{{ $tuan }}">Tuần {{ $tuan }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($selectedNam && $selectedTuan)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                    <select
                        wire:model.live="selectedKhoaHoc"
                        class="fi-input w-72 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">-- Chọn khóa học --</option>
                        @foreach($availableKhoaHocs as $kh)
                            <option value="{{ $kh->id }}">
                                {{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($selectedKhoaHoc)
                <div class="flex flex-wrap items-end gap-2">
                    <button
                        wire:click="chuanBiChuyenKetQua"
                        class="fi-btn rounded-lg px-4 py-2 bg-[#CCFFD8] text-[#00529C] hover:bg-[#b8f5c7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7cdf9c]"
                        @disabled(!$coTheChinhSua || $daChuyenKetQua)
                    >
                        <span wire:loading.remove wire:target="chuanBiChuyenKetQua">Chuyển kết quả</span>
                        <span wire:loading wire:target="chuanBiChuyenKetQua">Đang xử lý...</span>
                    </button>

                    <button
                        wire:click="luuTamThoi"
                        class="fi-btn rounded-lg px-4 py-2 bg-[#FFFCD5] text-[#00529C] border border-[#eedb8d] hover:bg-[#f6f0bc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#eedb8d]"
                        @disabled(!$coTheChinhSua || $daChuyenKetQua)
                    >
                        <span wire:loading.remove wire:target="luuTamThoi">Lưu tạm</span>
                        <span wire:loading wire:target="luuTamThoi">Đang lưu...</span>
                    </button>

                    <button
                        wire:click="xuatExcelDanhSachHocVien"
                        class="fi-btn rounded-lg px-4 py-2 bg-[#FFFCD5] text-[#00529C] hover:bg-[#f6f0bc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#eedb8d]"
                    >
                        <span wire:loading.remove wire:target="xuatExcelDanhSachHocVien">Xuất Excel danh sách</span>
                        <span wire:loading wire:target="xuatExcelDanhSachHocVien">Đang xuất...</span>
                    </button>

                    <button
                        wire:click="moModalGuiEmail"
                        class="fi-btn rounded-lg px-4 py-2 bg-[#FFFCD5] text-[#00529C] hover:bg-[#f6f0bc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#eedb8d] flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Gửi Email
                    </button>
                </div>
            @endif
        </div>

        {{-- BẢNG LIỆT KÊ KHÓA HỌC TRONG NĂM --}}
        @if($selectedNam && !$selectedKhoaHoc)
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold">
                        Danh sách Khóa học trong năm {{ $selectedNam }}
                    </h3>

                    <button
                        wire:click="xuatExcelDanhSachKhoaHocTrongNam"
                        class="fi-btn rounded-lg px-4 py-2 bg-[#FFFCD5] text-[#00529C] hover:bg-[#f6f0bc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#eedb8d]"
                    >
                        <span wire:loading.remove wire:target="xuatExcelDanhSachKhoaHocTrongNam">Xuất Excel</span>
                        <span wire:loading wire:target="xuatExcelDanhSachKhoaHocTrongNam">Đang xuất...</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed text-sm border">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="w-14 px-4 py-2 border-b">STT</th>
                                <th class="w-36 px-4 py-2 border-b">Mã khóa</th>
                                <th class="px-4 py-2 border-b">Tên khóa học</th>
                                <th class="w-36 px-4 py-2 border-b text-center">Trạng thái</th>
                                <th class="w-20 px-4 py-2 border-b text-center">Số buổi</th>
                                <th class="w-40 px-4 py-2 border-b">Tuần</th>
                                <th class="w-56 px-4 py-2 border-b text-center">Ngày đào tạo</th>
                                <th class="w-64 px-4 py-2 border-b text-center">Giảng viên</th>
                                <th class="w-28 px-4 py-2 border-b text-center">Số lượng HV</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($khoaHocYearRows as $i => $row)
                                <tr
                                    class="hover:bg-gray-50 cursor-pointer"
                                    @if(isset($row['khoa_hoc_id']))
                                        wire:click.prevent="$set('selectedKhoaHoc', {{ (int) $row['khoa_hoc_id'] }})"
                                    @endif
                                >
                                    <td class="px-4 py-3 text-center">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $row['ma_khoa_hoc'] }}</td>
                                    <td class="px-4 py-3 break-words">{{ $row['ten_khoa_hoc'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusStyles = [
                                                'Dự thảo' => 'bg-gray-100 text-gray-800',
                                                'Ban hành' => 'bg-blue-100 text-blue-800',
                                                'Đang đào tạo' => 'bg-yellow-100 text-yellow-800',
                                                'Kết thúc' => 'bg-green-100 text-green-800',
                                                'Tạm hoãn' => 'bg-red-100 text-red-800',
                                            ];
                                            $badgeClass = $statusStyles[$row['trang_thai']] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap {{ $badgeClass }}">
                                            {{ $row['trang_thai'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $row['so_buoi'] }}</td>
                                    <td class="px-4 py-3 break-words">{{ $row['tuan'] }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">{{ $row['ngay_dao_tao'] }}</td>
                                    <td class="px-4 py-3 text-center break-words">{{ $row['giang_vien'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['so_luong_hv'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        Không có khóa học nào trong năm {{ $selectedNam }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- THÔNG TIN & DANH SÁCH HỌC VIÊN --}}
        @if($selectedKhoaHoc)
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-4 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <h3 class="text-lg font-semibold">Danh sách học viên đã ghi danh</h3>
                        <div class="text-sm text-gray-600 space-x-4">
                            <span>Yêu cầu % giờ học: <strong>{{ $khoaHocRequirements['yeu_cau_gio'] ?? '—' }}%</strong></span>
                            <span>Yêu cầu điểm TB: <strong>{{ $khoaHocRequirements['yeu_cau_diem'] ?? '—' }}</strong></span>
                            <span>Tổng giờ kế hoạch: <strong>{{ $this->formatDecimal($khoaHocRequirements['tong_gio_ke_hoach']) }}</strong></span>
                        </div>
                    </div>

                    @if($daChuyenKetQua)
                        <div class="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-700">
                            Kết quả của khóa học đã được chuyển. Bảng đánh giá đang ở chế độ chỉ xem.
                        </div>
                    @elseif(!$coTheChinhSua)
                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700">
                            Bạn không có quyền chỉnh sửa kết quả của khóa học này.
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                        <span class="font-semibold">Ẩn/hiện cột:</span>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.tt" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>TT</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.ma_so" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>Mã số</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.ho_ten" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>Họ &amp; Tên</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.dtb" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>ĐTB</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.ket_qua" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>Kết quả</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" wire:model.live="columnVisibility.hanh_dong" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>Hành động</span>
                        </label>
                        @foreach($khoaHocLichHocs as $lichHocId => $lichHoc)
                            <label class="inline-flex items-center gap-1">
                                <input type="checkbox" wire:model.live="sessionColumnVisibility.{{ $lichHocId }}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span>Buổi {{ $lichHoc['nhan'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto relative">
                        <table class="min-w-[1100px] w-full text-sm border">
                            <thead class="bg-gray-100">
                                <tr>
                                    @if($columnVisibility['tt'] ?? true)
                                        <th class="px-3 py-2 border-b align-middle text-center" rowspan="2">TT</th>
                                    @endif
                                    @if($columnVisibility['ma_so'] ?? true)
                                        <th class="px-3 py-2 border-b align-middle text-center" rowspan="2">Mã số</th>
                                    @endif
                                    @if($columnVisibility['ho_ten'] ?? true)
                                        <th class="px-3 py-2 border-b align-middle" rowspan="2">Họ &amp; Tên</th>
                                    @endif
                                    @foreach($khoaHocLichHocs as $lichHocId => $lichHoc)
                                        @if($sessionColumnVisibility[$lichHocId] ?? true)
                                            <th class="px-3 py-2 border-b text-center align-middle min-w-[200px]" rowspan="1">
                                                {{ $lichHoc['nhan'] }}
                                            </th>
                                        @endif
                                    @endforeach
                                    @if($columnVisibility['dtb'] ?? true)
                                        <th class="w-28 px-3 py-2 border-b text-center align-middle" rowspan="2">ĐTB</th>
                                    @endif
                                    @if($columnVisibility['ket_qua'] ?? true)
                                        <th class="w-44 min-w-[11rem] px-3 py-2 border-b text-center align-middle" rowspan="2">Kết quả</th>
                                    @endif
                                    @if($columnVisibility['hanh_dong'] ?? true)
                                        <th class="w-28 px-3 py-2 border-b text-center align-middle" rowspan="2">Hành động</th>
                                    @endif
                                </tr>
                                <tr>
                                    @foreach($khoaHocLichHocs as $lichHocId => $lichHoc)
                                        @if($sessionColumnVisibility[$lichHocId] ?? true)
                                            <th class="px-3 py-2 border-b text-xs text-gray-600 min-w-[200px] text-center">
                                                {{ $lichHoc['mo_ta'] }}
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($hocVienRows as $index => $row)
                                    @php
                                        $hocVien = $row['hoc_vien'];
                                        $dangKyId = $row['dang_ky_id'];
                                        $editing = $dangKyId ? ($isEditing[$dangKyId] ?? false) : false;
                                        $tongKet = $dangKyId ? ($tongKetData[$dangKyId] ?? []) : [];
                                        $ketQuaNhan = $tongKet['ket_qua'] ?? '';
                                        $ketQuaGoiY = $tongKet['ket_qua_goi_y'] ?? null;
                                        $ketQuaGoiYLabel = $ketQuaGoiY === 'khong_hoan_thanh' ? 'Không hoàn thành' : 'Hoàn thành';
                                    @endphp
                                    <tr class="align-top">
                                        @if($columnVisibility['tt'] ?? true)
                                            <td class="px-3 py-3 text-center">{{ $index + 1 }}</td>
                                        @endif
                                        @if($columnVisibility['ma_so'] ?? true)
                                            <td class="px-3 py-3 font-medium text-gray-900 text-center">{{ $hocVien->msnv }}</td>
                                        @endif
                                        @if($columnVisibility['ho_ten'] ?? true)
                                            <td class="px-3 py-3 break-words">{{ $hocVien->ho_ten }}</td>
                                        @endif

                                        @foreach($khoaHocLichHocs as $lichHocId => $lichHoc)
                                            @if($sessionColumnVisibility[$lichHocId] ?? true)
                                                @php
                                                    $cellKey = $dangKyId ? ($diemDanhData[$dangKyId][$lichHocId] ?? null) : null;
                                                    $status = $cellKey['trang_thai'] ?? 'co_mat';
                                                    $lyDo = trim((string)($cellKey['ly_do_vang'] ?? ''));
                                                    $soGio = $cellKey['so_gio_hoc'] ?? null;
                                                    $diem  = $cellKey['diem'] ?? null;
                                                    $statusLabel = ['co_mat' => 'Có mặt', 'vang_phep' => 'Vắng P', 'vang_khong_phep' => 'Vắng KP'][$status] ?? 'Có mặt';
                                                @endphp
                                                <td class="px-3 py-3 align-top min-w-[200px] text-center text-sm">
                                                    @if($editing)
                                                        <div class="space-y-2">
                                                            <select
                                                                wire:model.live="diemDanhData.{{ $dangKyId }}.{{ $lichHocId }}.trang_thai"
                                                                class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                            >
                                                                <option value="co_mat">Có mặt</option>
                                                                <option value="vang_phep">Vắng P</option>
                                                                <option value="vang_khong_phep">Vắng KP</option>
                                                            </select>

                                                            @if($status !== 'co_mat')
                                                                <input
                                                                    type="text"
                                                                    wire:model.live="diemDanhData.{{ $dangKyId }}.{{ $lichHocId }}.ly_do_vang"
                                                                    class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                                    placeholder="Lý do vắng"
                                                                >
                                                            @else
                                                                <div class="grid grid-cols-1 gap-2">
                                                                    <input
                                                                        type="number"
                                                                        step="0.1"
                                                                        min="0"
                                                                        wire:model.live="diemDanhData.{{ $dangKyId }}.{{ $lichHocId }}.so_gio_hoc"
                                                                        class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                                        placeholder="Số giờ"
                                                                    >

                                                                    <input
                                                                        type="number"
                                                                        step="0.1"
                                                                        min="0"
                                                                        max="10"
                                                                        wire:model.live="diemDanhData.{{ $dangKyId }}.{{ $lichHocId }}.diem"
                                                                        class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                                        placeholder="Điểm"
                                                                    >
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="space-y-1 text-gray-700 whitespace-normal break-words">
                                                            @if($status === 'co_mat')
                                                                <div class="font-medium">
                                                                    {{ $statusLabel }} ({{ $this->formatDecimal($soGio) }} giờ) - Điểm: {{ $this->formatDecimal($diem) }}
                                                                </div>
                                                            @else
                                                                <div class="font-medium">{{ $statusLabel }}</div>
                                                                @if($lyDo !== '')
                                                                    <div class="text-gray-500">{{ $lyDo }}</div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach

                                        @if($columnVisibility['dtb'] ?? true)
                                            <td class="px-3 py-3 text-center align-middle">
                                                <span class="font-semibold">{{ $this->formatDecimal($tongKet['diem_trung_binh'] ?? null) }}</span>
                                            </td>
                                        @endif

                                        @if($columnVisibility['ket_qua'] ?? true)
                                            <td class="px-3 py-3 align-top w-44 min-w-[11rem] text-center">
                                                @if($editing)
                                                    <select
                                                        wire:model.live="tongKetData.{{ $dangKyId }}.ket_qua"
                                                        class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    >
                                                        <option value="hoan_thanh">Hoàn thành</option>
                                                        <option value="khong_hoan_thanh">Không hoàn thành</option>
                                                    </select>
                                                    @if($ketQuaGoiY)
                                                        <p class="mt-1 text-xs text-gray-500 whitespace-normal">Gợi ý: {{ $ketQuaGoiYLabel }}</p>
                                                    @endif
                                                @else
                                                    <span class="px-2 py-1 inline-flex items-center justify-center rounded text-xs font-semibold w-full {{ ($ketQuaNhan ?? '') === 'hoan_thanh' ? 'bg-[#CCFFD8] text-[#00529C]' : 'bg-red-100 text-red-700' }}">
                                                        {{ $ketQuaNhan === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành' }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        @if($columnVisibility['hanh_dong'] ?? true)
                                            <td class="px-3 py-3 text-center align-middle">
                                                @if($dangKyId)
                                                    @if($editing)
                                                        <button
                                                            type="button"
                                                            wire:click="dongDiemDanh({{ $dangKyId }})"
                                                            class="fi-btn fi-btn-secondary fi-btn-sm rounded-lg px-3 py-1 bg-gray-100 text-gray-800 hover:bg-gray-200"
                                                        >
                                                            Đóng
                                                        </button>
                                                    @else
                                                        @if($coTheChinhSua && !$daChuyenKetQua)
                                                            <button
                                                                type="button"
                                                                wire:click="moSuaDiemDanh({{ $dangKyId }})"
                                                                class="fi-btn fi-btn-sm rounded-lg px-3 py-1 border border-gray-300 text-black bg-white hover:bg-gray-100"
                                                            >
                                                                Sửa
                                                            </button>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $this->visibleColumnCount }}" class="px-3 py-6 text-center text-gray-500">
                                            Chưa có học viên nào được ghi danh
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal gửi email --}}
    @if($showGuiEmailModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold mb-4">Gửi Email Hàng Loạt</h3>
                <form wire:submit.prevent="guiEmailHangLoat">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Chọn Mẫu Email <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model="selectedEmailTemplateId"
                                class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">-- Chọn mẫu --</option>
                                @foreach(\App\Models\EmailTemplate::all() as $template)
                                    <option value="{{ $template->id }}">{{ $template->ten_mau }}</option>
                                @endforeach
                            </select>
                            @error('selectedEmailTemplateId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Chọn Tài khoản Gửi Email <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model="selectedEmailAccountId"
                                class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">-- Chọn tài khoản --</option>
                                @foreach(\App\Models\EmailAccount::where('active', 1)->get() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->email }})</option>
                                @endforeach
                            </select>
                            @error('selectedEmailAccountId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gửi cho</label>
                            <select
                                wire:model="loaiEmail"
                                class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="hoc_vien">Học viên</option>
                                <option value="giang_vien">Giảng viên</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 text-sm text-gray-500">
                        <p>Số học viên sẽ nhận email: <span class="font-semibold">{{ count($hocViensDaDangKy) }}</span></p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            wire:click="$set('showGuiEmailModal', false)"
                        >
                            Hủy
                        </button>
                        <button
                            type="submit"
                            class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        >
                            <span wire:loading.remove wire:target="guiEmailHangLoat">Gửi Email</span>
                            <span wire:loading wire:target="guiEmailHangLoat">Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal xác nhận chuyển kết quả --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold mb-4">Xác nhận chuyển kết quả</h3>
                <p class="text-sm text-gray-600">
                    Việc chuyển kết quả sẽ hoàn tất việc Đánh giá học viên, Giảng viên không thể sửa kết quả sau khi chuyển.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        wire:click="$set('showConfirmModal', false)"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        wire:click="xacNhanChuyenKetQua"
                        class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
