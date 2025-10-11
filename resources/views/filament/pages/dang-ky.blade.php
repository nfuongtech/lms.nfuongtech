<x-filament::page>
    <div class="space-y-6 font-sans text-sm">

        {{-- Bộ lọc Khóa học: Năm, Tuần, Trạng thái và Chọn Khóa học trên cùng 1 dòng --}}
        <div class="flex flex-wrap items-end gap-4 text-sm">
            {{-- Chọn Năm --}}
            <div class="w-24 min-w-[100px]">
                <label class="block font-medium text-gray-700">Năm</label>
                <select wire:model.live="selectedNam" class="fi-input w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @foreach($this->danhSachNam as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Chọn Tuần --}}
            <div class="w-32 min-w-[120px]">
                <label class="block font-medium text-gray-700">Tuần - Năm</label>
                <select wire:model.live="selectedTuan" class="fi-input w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Tất cả</option>
                    @foreach($this->danhSachTuan as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Trạng thái kế hoạch --}}
            <div class="w-52 min-w-[220px]">
                <label class="block font-medium text-gray-700">Trạng thái Kế hoạch đào tạo</label>
                <select wire:model.live="selectedTrangThaiKeHoach" class="fi-input w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Tất cả --</option>
                    <option value="Dự thảo">Dự thảo</option>
                    <option value="Ban hành">Ban hành</option>
                    <option value="Đang đào tạo">Đang đào tạo</option>
                    <option value="Kết thúc">Kết thúc</option>
                    <option value="Tạm hoãn">Tạm hoãn</option>
                </select>
            </div>

            {{-- Chọn Khóa học --}}
            <div class="flex-1 min-w-[250px]">
                <label class="block font-medium text-gray-700">Khóa học</label>
                <div class="relative">
                    <select
                        wire:model.live="selectedKhoaHoc"
                        class="fi-input w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 appearance-none bg-white"
                        style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; fill=&quot;none&quot; viewBox=&quot;0 0 20 20&quot;><path stroke=&quot;%236b7280&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;1.5&quot; d=&quot;M6 8l4 4 4-4&quot;/></svg>'); background-position: right 0.5rem center; background-repeat: no-repeat; padding-right: 2rem;"
                    >
                        <option value="">-- Chọn Khóa học --</option>
                        @foreach($this->danhSachKhoaHocLoc as $kh)
                            <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Ô nhập MSNV --}}
        <div class="bg-white shadow rounded-xl p-5 border border-gray-200">
            <label class="block text-sm font-semibold text-gray-800">Nhập MSNV (phân tách bằng dấu phẩy)</label>
            <div class="flex flex-col sm:flex-row sm:items-end gap-3 mt-2">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.500ms="msnvInput" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                           placeholder="VD: HV01, HV02, HV03">
                </div>
                <button wire:click="store"
                        class="fi-btn rounded-lg px-4 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 sm:self-end sm:ml-auto mt-2 sm:mt-0"
                        style="background-color: #FFFCD5; color: #00529C; border: 1px solid #00529C;"
                        >
                    <span wire:loading.remove class="text-sm">Ghi danh</span>
                    <span wire:loading class="text-sm">Đang xử lý...</span>
                </button>
            </div>

            @if($parsedHocViens)
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                    <p class="font-medium">Danh sách tìm thấy ({{ count($parsedHocViens) }}):</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        @foreach($parsedHocViens as $hv)
                            <li>{{ $hv['display'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($parsedMsnvNotFound)
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <p class="font-medium">MSNV không tồn tại ({{ count($parsedMsnvNotFound) }}):</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        @foreach($parsedMsnvNotFound as $msnv)
                            <li class="flex items-center justify-between">
                                <span>{{ $msnv }}</span>
                                <button class="fi-btn fi-btn-xs fi-btn-primary ml-2 rounded bg-primary-100 text-primary-800 hover:bg-primary-200"
                                        wire:click="$set('newHocVien.msnv', '{{ $msnv }}'); $set('showAddHocVienModal', true)">
                                    Thêm mới
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Block 1: Thông tin Khóa học --}}
        @if($selectedKhoaHoc)
            @php
                $kh = \App\Models\KhoaHoc::with('chuongTrinh', 'lichHocs.giangVien')->find($selectedKhoaHoc);

                // Tổng giờ khóa học (theo chương trình áp dụng)
                $tongGioKhoaHoc = 0;
                if ($kh && $kh->chuongTrinh) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('chuong_trinhs', 'so_gio')) {
                        $tongGioKhoaHoc = \App\Models\ChuongTrinh::where('id', $kh->chuong_trinh_id)
                            ->where('tinh_trang', 'Đang áp dụng')->sum('so_gio');
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('chuong_trinhs', 'thoi_luong')) {
                        $tongGioKhoaHoc = \App\Models\ChuongTrinh::where('id', $kh->chuong_trinh_id)
                            ->where('tinh_trang', 'Đang áp dụng')->sum('thoi_luong');
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('chuong_trinhs', 'gio')) {
                        $tongGioKhoaHoc = \App\Models\ChuongTrinh::where('id', $kh->chuong_trinh_id)
                            ->where('tinh_trang', 'Đang áp dụng')->sum('gio');
                    } else {
                        $tongGioKhoaHoc = $kh->lichHocs->sum(function ($lich) {
                            return isset($lich->so_gio_giang) ? max((float) $lich->so_gio_giang, 0) : max((float) ($lich->thoi_luong ?? 0), 0);
                        });
                    }
                }

                // Tổng giờ theo kế hoạch (tính theo từng chuyên đề đã sắp lịch)
                $tongGioTheoKeHoach = $kh->lichHocs->reduce(function ($carry, $lich) {
                    if (isset($lich->so_gio_giang) && $lich->so_gio_giang !== null) {
                        $carry += max((float) $lich->so_gio_giang, 0);
                    } elseif (isset($lich->thoi_luong)) {
                        $carry += max((float) $lich->thoi_luong, 0);
                    } elseif (!empty($lich->gio_bat_dau) && !empty($lich->gio_ket_thuc)) {
                        try {
                            $start = \Carbon\Carbon::parse($lich->gio_bat_dau);
                            $end = \Carbon\Carbon::parse($lich->gio_ket_thuc);
                            $carry += max($end->diffInMinutes($start) / 60, 0);
                        } catch (\Throwable $e) {
                            // Bỏ qua khi định dạng thời gian không hợp lệ
                        }
                    }
                    return $carry;
                }, 0.0);
            @endphp
            <div class="bg-white shadow rounded-xl p-5 border border-gray-200">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Thông tin khóa học</h3>
                    <div class="flex flex-wrap gap-2 text-sm">
                        @if($hocViensDaDangKy->count() > 0 || $kh->lichHocs->pluck('giangVien')->filter()->count() > 0)
                            <button wire:click="moModalGuiEmail"
                                    class="fi-btn fi-btn-secondary rounded-lg px-3 py-1.5 text-sm bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Gửi Email
                            </button>
                        @endif
                        {{-- Nút Xuất Excel --}}
                        <button wire:click="xuatThongTinKhoaHoc"
                                class="fi-btn fi-btn-success rounded-lg px-3 py-1.5 text-sm bg-green-100 text-green-800 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Xuất TT Khóa học
                        </button>
                        <button wire:click="xuatDanhSachHocVien"
                                class="fi-btn fi-btn-success rounded-lg px-3 py-1.5 text-sm bg-green-100 text-green-800 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Xuất DS Học viên
                        </button>
                        {{-- Hết Nút Xuất Excel --}}
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-gray-700 border-collapse text-sm min-w-[900px]">
                        <thead class="bg-gray-50 text-gray-900">
                            <tr>
                                <th class="px-4 py-2 border-b">Mã Khóa/Lớp</th>
                                <th class="px-4 py-2 border-b">Tên khóa học</th>
                                <th class="px-4 py-2 border-b">Tổng giờ khóa học</th>
                                <th class="px-4 py-2 border-b">Tổng giờ theo kế hoạch</th>
                                <th class="px-4 py-2 border-b">Giảng viên</th>
                                <th class="px-4 py-2 border-b">Thời gian đào tạo</th>
                                <th class="px-4 py-2 border-b">Trạng thái</th>
                                <th class="px-4 py-2 border-b">Số HV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $kh->ma_khoa_hoc }}</td>
                                <td class="px-4 py-3">{{ $kh->ten_khoa_hoc }}</td>
                                <td class="px-4 py-3">{{ number_format((float) $tongGioKhoaHoc, 1) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colorClass = ($tongGioTheoKeHoach + 0.0001 < $tongGioKhoaHoc) ? 'text-red-600 font-semibold' : 'text-gray-700';
                                        $displayKeHoach = number_format(max($tongGioTheoKeHoach, 0), 1);
                                    @endphp
                                    <span class="{{ $colorClass }}">{{ $displayKeHoach }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $kh->lichHocs->pluck('giangVien.ho_ten')->filter()->join(', ') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($kh->lichHocs->isNotEmpty())
                                        @php
                                            $lichDau = $kh->lichHocs->first();
                                            $ngay = $lichDau->ngay_hoc ? date('d/m/Y', strtotime($lichDau->ngay_hoc)) : 'N/A';
                                            $gioBatDau = $lichDau->gio_bat_dau ? date('H:i', strtotime($lichDau->gio_bat_dau)) : '';
                                            $gioKetThuc = $lichDau->gio_ket_thuc ? date('H:i', strtotime($lichDau->gio_ket_thuc)) : '';
                                        @endphp
                                        {{ $ngay }}, {{ $gioBatDau }}-{{ $gioKetThuc }}
                                    @else
                                        Chưa có lịch
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $trangThai = $kh->trang_thai_hien_thi ?? $kh->trang_thai;
                                        $statusStyles = [
                                            'Dự thảo' => 'bg-gray-100 text-gray-800',
                                            'Ban hành' => 'bg-blue-100 text-blue-800',
                                            'Đang đào tạo' => 'bg-yellow-100 text-yellow-800',
                                            'Kết thúc' => 'bg-green-100 text-green-800',
                                            'Tạm hoãn' => 'bg-red-100 text-red-800',
                                        ];
                                        $badgeClass = $statusStyles[$trangThai] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $badgeClass }}">
                                        {{ $trangThai }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $hocViensDaDangKy->count() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Block 2: Học viên đã ghi danh --}}
        <div class="bg-white shadow rounded-xl p-5 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Danh sách học viên đã ghi danh ({{ $hocViensDaDangKy->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-gray-700 border-collapse text-sm min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-900">
                        <tr>
                            <th class="px-4 py-2 border-b">MSNV</th>
                            <th class="px-4 py-2 border-b">Họ & Tên</th>
                            <th class="px-4 py-2 border-b">Năm sinh</th>
                            <th class="px-4 py-2 border-b">Chức vụ</th>
                            <th class="px-4 py-2 border-b">Đơn vị</th>
                            <th class="px-4 py-2 border-b">Email</th>
                            <th class="px-4 py-2 border-b text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hocViensDaDangKy as $dk)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $dk->hocVien->msnv }}</td>
                                <td class="px-4 py-3">{{ $dk->hocVien->ho_ten }}</td>
                                <td class="px-4 py-3">{{ $dk->hocVien->nam_sinh ? date('d/m/Y', strtotime($dk->hocVien->nam_sinh)) : 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $dk->hocVien->chuc_vu }}</td>
                                <td class="px-4 py-3">{{ $dk->hocVien->donVi->ten_hien_thi ?? '' }}</td>
                                <td class="px-4 py-3">{{ $dk->hocVien->email ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button wire:click="guiLaiEmailChoHocVien({{ $dk->id }})"
                                                class="fi-btn fi-btn-xs fi-btn-info rounded-lg px-2 py-1 text-xs bg-blue-100 text-blue-800 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                title="Gửi lại email">
                                            Gửi lại
                                        </button>
                                        <button wire:click="deleteDangKy({{ $dk->id }})"
                                                wire:confirm="Bạn có chắc chắn muốn xóa đăng ký này?"
                                                class="fi-btn fi-btn-xs fi-btn-danger rounded-lg px-2 py-1 text-xs bg-red-100 text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    <span class="block">Chưa có học viên nào được ghi danh</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal thêm học viên mới --}}
    @if($showAddHocVienModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Thêm học viên mới</h3>
                <form wire:submit.prevent="saveNewHocVien">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">MSNV <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="newHocVien.msnv" placeholder="MSNV" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('newHocVien.msnv') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Họ và tên <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="newHocVien.ho_ten" placeholder="Họ và tên" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('newHocVien.ho_ten') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Năm sinh (dd/mm/yyyy)</label>
                            <input type="text" wire:model="newHocVien.nam_sinh" placeholder="dd/mm/yyyy" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('newHocVien.nam_sinh') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="newHocVien.email" placeholder="Email" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('newHocVien.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chức vụ</label>
                            <input type="text" wire:model="newHocVien.chuc_vu" placeholder="Chức vụ" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Đơn vị</label>
                            <select wire:model="newHocVien.don_vi_id" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn đơn vị --</option>
                                @foreach(\App\Models\DonVi::all() as $dv)
                                    <option value="{{ $dv->id }}">{{ $dv->ten_hien_thi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                wire:click="$set('showAddHocVienModal', false)">
                            Hủy
                        </button>
                        <button type="submit" class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <span wire:loading.remove wire:target="saveNewHocVien">Lưu</span>
                            <span wire:loading wire:target="saveNewHocVien">Đang lưu...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal gửi email --}}
    @if($showGuiEmailModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gửi Email</h3>
                <form wire:submit.prevent="guiEmailHangLoat">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loại Email <span class="text-red-500">*</span></label>
                            <select wire:model="loaiEmail" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="hoc_vien">Gửi cho Học viên</option>
                                <option value="giang_vien">Gửi cho Giảng viên</option>
                            </select>
                            @error('loaiEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chọn Mẫu Email <span class="text-red-500">*</span></label>
                            <select wire:model="selectedEmailTemplateId" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn mẫu --</option>
                                @foreach($this->getEmailTemplates() as $template)
                                    <option value="{{ $template->id }}">{{ $template->ten_mau ?? $template->tieu_de ?? 'Mẫu #' . $template->id }}</option>
                                @endforeach
                            </select>
                            @error('selectedEmailTemplateId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chọn Tài Khoản Gửi <span class="text-red-500">*</span></label>
                            <select wire:model="selectedEmailAccountId" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn tài khoản --</option>
                                @foreach($this->getEmailAccounts() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->email }})</option>
                                @endforeach
                            </select>
                            @error('selectedEmailAccountId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6 text-sm text-gray-500">
                        @if($loaiEmail === 'hoc_vien')
                            <p>Số học viên sẽ nhận email: <span class="font-semibold">{{ $hocViensDaDangKy->count() }}</span></p>
                        @elseif($loaiEmail === 'giang_vien')
                            <p>Số giảng viên sẽ nhận email: <span class="font-semibold">{{ $this->getDanhSachGiangVien()->count() }}</span></p>
                        @endif
                        @if($hocViensDaDangKy->isEmpty() && $this->getDanhSachGiangVien()->isEmpty())
                            <p class="mt-2 text-sm text-yellow-600">Chú ý: Hiện không có học viên/giảng viên nào để gửi — bạn vẫn có thể chọn mẫu và kiểm tra nội dung.</p>
                        @endif
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                wire:click="$set('showGuiEmailModal', false)">
                            Hủy
                        </button>
                        <button type="submit" class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <span wire:loading.remove wire:target="guiEmailHangLoat">Gửi Email</span>
                            <span wire:loading wire:target="guiEmailHangLoat">Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament::page>
