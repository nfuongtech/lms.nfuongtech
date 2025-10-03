{{-- resources/views/filament/pages/diem-danh-hoc-vien.blade.php --}}
<x-filament::page>
    <div class="space-y-6">

        {{-- BỘ LỌC --}}
        <div class="flex flex-wrap items-end gap-4">
            {{-- Năm --}}
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

            {{-- Tuần --}}
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

            {{-- Khóa học --}}
            @if($selectedNam && $selectedTuan)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                    <select
                        wire:model.live="selectedKhoaHoc"
                        class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">-- Chọn --</option>
                        @foreach($availableKhoaHocs as $kh)
                            <option value="{{ $kh->id }}">
                                {{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Buổi học --}}
            @if($selectedKhoaHoc)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Buổi học</label>
                    <select
                        wire:model.live="selectedLichHoc"
                        class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">-- Chọn buổi học --</option>
                        @foreach($availableLichHocs as $lh)
                            <option value="{{ $lh->id }}">
                                {{ $lh->chuyenDe->ten_chuyen_de ?? 'N/A' }} -
                                {{ $lh->ngay_hoc ? date('d/m/Y', strtotime($lh->ngay_hoc)) : 'N/A' }}
                                ({{ $lh->gio_bat_dau ? date('H:i', strtotime($lh->gio_bat_dau)) : 'N/A' }}-{{ $lh->gio_ket_thuc ? date('H:i', strtotime($lh->gio_ket_thuc)) : 'N/A' }})
                                tại {{ $lh->dia_diem ?? '...' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Hành động chung --}}
            @if($selectedKhoaHoc && $selectedLichHoc)
                <div class="flex items-end gap-2">
                    <button
                        wire:click="luuDiemDanh"
                        class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        <span wire:loading.remove>Lưu điểm danh</span>
                        <span wire:loading>Đang xử lý...</span>
                    </button>

                    <button
                        wire:click="moModalGuiEmail"
                        class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Gửi Email
                    </button>
                </div>
            @endif
        </div>

        {{-- BẢNG LIỆT KÊ KHÓA HỌC TRONG NĂM (ẨN khi đã chọn Khóa & Buổi để điểm danh) --}}
        @if($selectedNam && !($selectedKhoaHoc && $selectedLichHoc))
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">
                    Danh sách Khóa học trong năm {{ $selectedNam }}
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed text-sm border">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="w-14 px-4 py-2 border-b">STT</th>
                                <th class="w-36 px-4 py-2 border-b">Mã khóa</th>
                                <th class="px-4 py-2 border-b">Tên khóa học</th>
                                <th class="w-36 px-4 py-2 border-b">Trạng thái</th>
                                <th class="w-20 px-4 py-2 border-b text-center">Số buổi</th>
                                <th class="w-40 px-4 py-2 border-b">Tuần</th>
                                <th class="w-56 px-4 py-2 border-b">Ngày đào tạo</th>
                                <th class="w-64 px-4 py-2 border-b">Giảng viên</th>
                                <th class="w-28 px-4 py-2 border-b text-center">Số lượng HV</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($khoaHocYearRows as $i => $row)
                                {{-- Click vào dòng khóa học để "mở" phần buổi học bên dưới --}}
                                <tr
                                    class="hover:bg-gray-50 cursor-pointer"
                                    @if(isset($row['khoa_hoc_id']))
                                        wire:click.prevent="$set('selectedKhoaHoc', {{ (int) $row['khoa_hoc_id'] }}); $set('selectedTuan', null); $set('selectedLichHoc', null)"
                                    @endif
                                >
                                    <td class="px-4 py-3 text-center">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $row['ma_khoa_hoc'] }}</td>
                                    <td class="px-4 py-3 break-words">{{ $row['ten_khoa_hoc'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $row['trang_thai'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $row['so_buoi'] }}</td>
                                    <td class="px-4 py-3 break-words">
                                        {{ $row['tuan'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $row['ngay_dao_tao'] }}</td>
                                    <td class="px-4 py-3 break-words">{{ $row['giang_vien'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['so_luong_hv'] }}</td>
                                </tr>

                                {{-- Dải BUỔI HỌC của khóa được click (năm hiện tại) --}}
                                @if(isset($row['khoa_hoc_id']) && $selectedKhoaHoc == $row['khoa_hoc_id'] && !empty($row['lichs']))
                                    <tr>
                                        <td colspan="9" class="px-4 py-3 bg-gray-50">
                                            <div class="text-sm text-gray-700 font-medium mb-2">Buổi học trong năm {{ $selectedNam }}:</div>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full table-fixed text-sm border bg-white">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="w-16 px-3 py-2 border-b">#</th>
                                                            <th class="w-40 px-3 py-2 border-b">Tuần</th>
                                                            <th class="w-44 px-3 py-2 border-b">Ngày</th>
                                                            <th class="w-40 px-3 py-2 border-b">Giờ</th>
                                                            <th class="px-3 py-2 border-b">Chuyên đề</th>
                                                            <th class="w-52 px-3 py-2 border-b">Địa điểm</th>
                                                            <th class="w-40 px-3 py-2 border-b text-center">Điểm danh</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($row['lichs'] as $j => $lh)
                                                            @php
                                                                $lhId = (int) ($lh['id'] ?? 0);
                                                                $lhTuan = (int) ($lh['tuan'] ?? 0);
                                                            @endphp
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-2 text-center">{{ $j + 1 }}</td>
                                                                <td class="px-3 py-2 text-center">Tuần {{ $lh['tuan'] ?? '' }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">
                                                                    {{ isset($lh['ngay_hoc']) ? date('d/m/Y', strtotime($lh['ngay_hoc'])) : '' }}
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap">
                                                                    {{ isset($lh['gio_bat_dau']) ? date('H:i', strtotime($lh['gio_bat_dau'])) : '' }}
                                                                    -
                                                                    {{ isset($lh['gio_ket_thuc']) ? date('H:i', strtotime($lh['gio_ket_thuc'])) : '' }}
                                                                </td>
                                                                <td class="px-3 py-2 break-words">
                                                                    {{ $lh['ten_chuyen_de'] ?? '' }}
                                                                </td>
                                                                <td class="px-3 py-2 break-words">
                                                                    {{ $lh['dia_diem'] ?? '' }}
                                                                </td>
                                                                <td class="px-3 py-2 text-center">
                                                                    {{-- GỌI HÀM LIVEWIRE để set đúng thứ tự + nạp dữ liệu --}}
                                                                    <button
                                                                        type="button"
                                                                        class="fi-btn fi-btn-primary fi-btn-sm rounded px-3 py-1 bg-primary-600 text-white hover:bg-primary-700"
                                                                        @click.stop
                                                                        wire:click="chonBuoiTuBangNam({{ (int) $row['khoa_hoc_id'] }}, {{ $lhTuan }}, {{ $lhId }})"
                                                                    >
                                                                        Chọn buổi này
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
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

        {{-- DANH SÁCH HỌC VIÊN (chỉ hiện khi đã chọn Khóa & Buổi) --}}
        @if($selectedKhoaHoc && $selectedLichHoc)
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Danh sách học viên đã ghi danh</h3>
                <div class="overflow-x-auto">
                    {{-- table-fixed + w-full để full màn hình & cột đồng đều --}}
                    <table class="min-w-full w-full table-fixed text-sm border">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="w-14 px-4 py-2 border-b">STT</th>
                                <th class="w-36 px-4 py-2 border-b">MSNV</th>
                                <th class="px-4 py-2 border-b">Họ & Tên</th>
                                <th class="w-48 px-4 py-2 border-b">Chức vụ</th>
                                <th class="w-56 px-4 py-2 border-b">Đơn vị</th>
                                {{-- Bỏ Đơn vị pháp nhân --}}
                                <th class="w-64 px-4 py-2 border-b">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hocViensDaDangKy as $index => $hocVien)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $hocVien->msnv }}</td>
                                    <td class="px-4 py-3 break-words">{{ $hocVien->ho_ten }}</td>
                                    <td class="px-4 py-3 break-words">{{ $hocVien->chuc_vu }}</td>
                                    <td class="px-4 py-3 break-words">{{ $hocVien->donVi->ten_hien_thi ?? '' }}</td>
                                    <td class="px-4 py-3 break-words">{{ $hocVien->email ?? '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Chưa có học viên nào được ghi danh
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- FORM ĐIỂM DANH TỪNG HỌC VIÊN --}}
        @if($selectedKhoaHoc && $selectedLichHoc && count($hocViensDaDangKy) > 0)
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Điểm danh từng học viên</h3>
                <form wire:submit.prevent="luuDiemDanh">
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full table-fixed text-sm border">
                            <thead class="bg-gray-100 text-left">
                                <tr>
                                    <th class="w-14 px-4 py-2 border-b">STT</th>
                                    <th class="w-36 px-4 py-2 border-b">MSNV</th>
                                    <th class="px-4 py-2 border-b">Họ & Tên</th>
                                    <th class="w-44 px-4 py-2 border-b">Tình trạng</th>
                                    <th class="w-64 px-4 py-2 border-b">Lý do vắng</th>
                                    <th class="w-36 px-4 py-2 border-b">Điểm buổi học</th>
                                    <th class="px-4 py-2 border-b">Đánh giá kỷ luật</th>
                                    <th class="w-32 px-4 py-2 border-b text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($hocViensDaDangKy as $index => $hocVien)
                                    @php
                                        $dangKy = $hocVien->dangKies->firstWhere('khoa_hoc_id', $selectedKhoaHoc);
                                        $dangKyId = $dangKy->id ?? ('new_' . $index);
                                        $editing = $isEditing[$dangKyId] ?? true;
                                        $tt = $diemDanhData[$dangKyId]['trang_thai'] ?? 'co_mat';
                                    @endphp
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $hocVien->msnv }}</td>
                                        <td class="px-4 py-3 break-words">{{ $hocVien->ho_ten }}</td>

                                        {{-- Tình trạng --}}
                                        <td class="px-4 py-3">
                                            @if($editing)
                                                <select
                                                    wire:model.live="diemDanhData.{{ $dangKyId }}.trang_thai"
                                                    class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                >
                                                    <option value="co_mat">Có mặt</option>
                                                    <option value="vang_phep">Vắng phép</option>
                                                    <option value="vang_khong_phep">Vắng không phép</option>
                                                </select>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                                                    {{ ['co_mat'=>'Có mặt','vang_phep'=>'Vắng phép','vang_khong_phep'=>'Vắng không phép'][$tt] }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Lý do vắng: Ẩn khi Có mặt; Bắt buộc khi Vắng phép --}}
                                        <td class="px-4 py-3">
                                            @if($editing)
                                                @if($tt === 'vang_phep')
                                                    <input
                                                        type="text"
                                                        wire:model.live="diemDanhData.{{ $dangKyId }}.ly_do_vang"
                                                        required
                                                        class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                        placeholder="Lý do vắng (bắt buộc)"
                                                    >
                                                @elseif($tt === 'co_mat')
                                                    <input type="text" disabled class="fi-input w-full bg-gray-100" placeholder="Không cần lý do">
                                                @else
                                                    <input
                                                        type="text"
                                                        wire:model.live="diemDanhData.{{ $dangKyId }}.ly_do_vang"
                                                        class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                        placeholder="(Tùy chọn)"
                                                    >
                                                @endif
                                            @else
                                                @if($tt === 'co_mat')
                                                    <span class="text-gray-400">—</span>
                                                @else
                                                    <span>{{ $diemDanhData[$dangKyId]['ly_do_vang'] ?? '' }}</span>
                                                @endif
                                            @endif
                                        </td>

                                        {{-- Điểm buổi học (số lẻ) --}}
                                        <td class="px-4 py-3">
                                            @if($editing)
                                                <input
                                                    type="number"
                                                    step="0.1" min="0" max="10"
                                                    wire:model.live="diemDanhData.{{ $dangKyId }}.diem_buoi_hoc"
                                                    class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    placeholder="Điểm..."
                                                >
                                            @else
                                                <span>{{ $diemDanhData[$dangKyId]['diem_buoi_hoc'] ?? '' }}</span>
                                            @endif
                                        </td>

                                        {{-- Đánh giá kỷ luật --}}
                                        <td class="px-4 py-3">
                                            @if($editing)
                                                <textarea
                                                    wire:model.live="diemDanhData.{{ $dangKyId }}.danh_gia_ky_luat"
                                                    class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    rows="2"
                                                    placeholder="Đánh giá kỷ luật..."
                                                ></textarea>
                                            @else
                                                <span class="whitespace-pre-line">{{ $diemDanhData[$dangKyId]['danh_gia_ky_luat'] ?? '' }}</span>
                                            @endif
                                        </td>

                                        {{-- Hành động: Đóng / Sửa (Sửa màu chữ đen) --}}
                                        <td class="px-4 py-3 text-center">
                                            @if($editing)
                                                <button
                                                    type="button"
                                                    wire:click="dongDiemDanh({{ $dangKyId }})"
                                                    class="fi-btn fi-btn-secondary fi-btn-sm rounded-lg px-3 py-1 bg-gray-100 text-gray-800 hover:bg-gray-200"
                                                >
                                                    Đóng
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="moSuaDiemDanh({{ $dangKyId }})"
                                                    class="fi-btn fi-btn-sm rounded-lg px-3 py-1 border border-gray-300 text-black bg-white hover:bg-gray-100"
                                                >
                                                    Sửa
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 text-center">
                        <button
                            type="submit"
                            class="fi-btn fi-btn-primary rounded-lg px-6 py-3 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        >
                            <span wire:loading.remove wire:target="luuDiemDanh">Lưu điểm danh</span>
                            <span wire:loading wire:target="luuDiemDanh">Đang lưu...</span>
                        </button>
                    </div>
                </form>
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
</x-filament::page>
