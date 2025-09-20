{{-- resources/views/filament/pages/diem-danh-hoc-vien.blade.php --}}
<x-filament::page>
    <div class="space-y-6">

        {{-- Bộ lọc --}}
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model.live="selectedKhoaHoc"
                        class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Chọn --</option>
                    @foreach(\App\Models\KhoaHoc::all() as $kh)
                        <option value="{{ $kh->id }}">
                            {{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedKhoaHoc)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tuần/Năm</label>
                    <select wire:model.live="selectedTuanNam"
                            class="fi-input w-48 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Chọn --</option>
                        @foreach($this->getDanhSachTuanNam() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Buổi học</label>
                    <select wire:model.live="selectedLichHoc"
                            class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Chọn buổi học --</option>
                        @if($selectedTuanNam)
                            @foreach($this->getDanhSachChuyenDeTheoTuanNam() as $lh)
                                <option value="{{ $lh['id'] }}">{{ $lh['display'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif

            @if($selectedKhoaHoc && $selectedLichHoc)
                <div class="flex items-end gap-2">
                    <button wire:click="luuDiemDanh"
                            class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <span wire:loading.remove>Lưu điểm danh</span>
                        <span wire:loading>Đang xử lý...</span>
                    </button>

                    <button wire:click="moModalGuiEmail"
                            class="fi-btn fi-btn-secondary rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 4.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Gửi Email
                    </button>
                </div>
            @endif
        </div>

        {{-- Block 1: Thông tin Khóa học --}}
        @if($selectedKhoaHoc)
            @php
                $kh = \App\Models\KhoaHoc::with('chuongTrinh', 'lichHocs.giangVien')->find($selectedKhoaHoc);
            @endphp
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3">Thông tin Khóa học</h3>
                <table class="min-w-full text-sm border">
                    <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-2 py-1">Mã Khóa/Lớp</th>
                        <th class="px-2 py-1">Tên chương trình</th>
                        <th class="px-2 py-1">Thời lượng (giờ)</th>
                        <th class="px-2 py-1">Giảng viên</th>
                        <th class="px-2 py-1">Thời gian đào tạo</th>
                        <th class="px-2 py-1">Trạng thái</th>
                        <th class="px-2 py-1">Số HV</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="px-2 py-1">{{ $kh->ma_khoa_hoc }}</td>
                        <td class="px-2 py-1">{{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</td>
                        <td class="px-2 py-1">{{ $kh->lichHocs->sum('thoi_luong') }}</td>
                        <td class="px-2 py-1">
                            {{ $kh->lichHocs->pluck('giangVien.ho_ten')->filter()->join(', ') }}
                        </td>
                        <td class="px-2 py-1">
                            {{ $kh->lichHocs->min('ngay_hoc') }} - {{ $kh->lichHocs->max('ngay_hoc') }}
                        </td>
                        <td class="px-2 py-1">{{ $kh->trang_thai }}</td>
                        <td class="px-2 py-1">{{ count($hocViensDaDangKy) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Block 2: Điểm danh học viên --}}
        @if($selectedKhoaHoc && $selectedLichHoc)
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3">Điểm danh học viên</h3>
                <form wire:submit.prevent="luuDiemDanh">
                    <table class="min-w-full text-sm border">
                        <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-2 py-1">STT</th>
                            <th class="px-2 py-1">MSNV</th>
                            <th class="px-2 py-1">Họ & Tên</th>
                            <th class="px-2 py-1">Trạng thái</th>
                            <th class="px-2 py-1">Lý do vắng</th>
                            <th class="px-2 py-1">Điểm buổi học</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($hocViensDaDangKy as $index => $hocVien)
                            <tr>
                                <td class="px-2 py-1 text-center">{{ $index + 1 }}</td>
                                <td class="px-2 py-1 font-medium">{{ $hocVien->msnv }}</td>
                                <td class="px-2 py-1">{{ $hocVien->ho_ten }}</td>
                                <td class="px-2 py-1">
                                    <select wire:model="diemDanhData.{{ $hocVien->dangKyId }}.trang_thai"
                                            class="fi-input w-full rounded border-gray-300">
                                        <option value="co_mat">Có mặt</option>
                                        <option value="vang_phep">Vắng có phép</option>
                                        <option value="vang_khong_phep">Vắng không phép</option>
                                    </select>
                                </td>
                                <td class="px-2 py-1">
                                    <input type="text"
                                           wire:model="diemDanhData.{{ $hocVien->dangKyId }}.ly_do_vang"
                                           class="fi-input w-full rounded border-gray-300"/>
                                </td>
                                <td class="px-2 py-1">
                                    <input type="number" min="0" max="10" step="0.1"
                                           wire:model="diemDanhData.{{ $hocVien->dangKyId }}.diem_buoi_hoc"
                                           class="fi-input w-20 rounded border-gray-300"/>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 py-2 text-center text-gray-500">
                                    Chưa có học viên nào để điểm danh
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        @endif

    </div>
</x-filament::page>
