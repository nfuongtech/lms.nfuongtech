{{-- resources/views/filament/pages/diem-danh.blade.php --}}
<x-filament::page>
    <div class="space-y-6">

        {{-- Bộ lọc --}}
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model.live="selectedKhoaHoc" class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">-- Chọn --</option>
                    @foreach(\App\Models\KhoaHoc::all() as $kh)
                        <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedKhoaHoc)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tuần/Năm</label>
                    <select wire:model.live="selectedTuanNam" class="fi-input w-48 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Chọn --</option>
                        @foreach($this->danhSachTuanNam as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Buổi học</label>
                    <select wire:model.live="selectedLichHoc" class="fi-input w-64 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Chọn buổi học --</option>
                        @if($selectedTuanNam)
                            @foreach($this->danhSachChuyenDeTheoTuanNam as $lh)
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
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Gửi Email
                    </button>
                </div>
            @endif
        </div>

        {{-- Ô nhập MSNV --}}
        <div class="bg-white shadow rounded-lg p-5 border border-gray-200">
            <label class="block text-sm font-semibold text-gray-800">Nhập MSNV (phân tách dấu phẩy)</label>
            <div class="flex flex-col sm:flex-row gap-3 mt-2">
                <input type="text" wire:model.live.debounce.500ms="msnvInput" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="VD: HV01, HV02, HV03">
                <button wire:click="store"
                        class="fi-btn fi-btn-primary rounded-lg px-4 py-2 bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <span wire:loading.remove>Ghi danh</span>
                    <span wire:loading>Đang xử lý...</span>
                </button>
            </div>

            {{-- Học viên tìm thấy --}}
            @if(isset($parsedHocViens) && count($parsedHocViens) > 0)
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                    <p class="font-medium">Danh sách tìm thấy ({{ count($parsedHocViens) }}):</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        @foreach($parsedHocViens as $hv)
                            <li>{{ $hv['display'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- MSNV không tìm thấy --}}
            @if(isset($parsedMsnvNotFound) && count($parsedMsnvNotFound) > 0)
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <p class="font-medium">MSNV không tồn tại ({{ count($parsedMsnvNotFound) }}):</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        @foreach($parsedMsnvNotFound as $msnv)
                            <li class="flex items-center justify-between">
                                <span>{{ $msnv }}</span>
                                <button class="fi-btn fi-btn-xs fi-btn-primary ml-2 rounded bg-primary-100 text-primary-800 hover:bg-primary-200"
                                        wire:click="$set('showAddHocVienModal', true)">
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
            @endphp
            <div class="bg-white shadow rounded-lg p-5 border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Thông tin Khóa học</h3>
                    @if($hocViensDaDangKy->count() > 0)
                    <button wire:click="moModalGuiEmail"
                            class="fi-btn fi-btn-secondary rounded-lg px-3 py-1.5 text-sm bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Gửi Email
                    </button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse border border-gray-200">
                        <thead class="bg-gray-50 text-gray-900">
                            <tr>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã Khóa/Lớp</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên chương trình</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời lượng (giờ)</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giảng viên</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian đào tạo</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số HV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $kh->ma_khoa_hoc }}</td>
                                <td class="px-4 py-3">{{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</td>
                                <td class="px-4 py-3">{{ $kh->lichHocs->sum('thoi_luong') }}</td>
                                <td class="px-4 py-3">
                                    {{ $kh->lichHocs->pluck('giangVien.ho_ten')->filter()->join(', ') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($kh->lichHocs->isNotEmpty())
                                        {{ $kh->lichHocs->min('ngay_hoc') }} - {{ $kh->lichHocs->max('ngay_hoc') }}
                                    @else
                                        Chưa có lịch
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ $kh->trang_thai }}
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
        <div class="bg-white shadow rounded-lg p-5 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Danh sách học viên đã ghi danh ({{ $hocViensDaDangKy->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse border border-gray-200">
                    <thead class="bg-gray-50 text-gray-900">
                        <tr>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MSNV</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ & Tên</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chức vụ</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tình trạng</th>
                            <th class="px-4 py-2 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hocViensDaDangKy as $index => $dk)
                            <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $dk->hocVien->msnv }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $dk->hocVien->ho_ten }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $dk->hocVien->chuc_vu }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $dk->hocVien->donVi->ten_hien_thi ?? '' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $dk->hocVien->email ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $dk->hocVien->tinh_trang === 'Đang làm việc' ? 'green' : 'red' }}-100 text-{{ $dk->hocVien->tinh_trang === 'Đang làm việc' ? 'green' : 'red' }}-800">
                                        {{ $dk->hocVien->tinh_trang }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="deleteDangKy({{ $dk->id }})"
                                            wire:confirm="Bạn có chắc chắn muốn xóa đăng ký này?"
                                            class="fi-btn fi-btn-danger fi-btn-sm rounded-lg px-3 py-1 text-xs bg-red-100 text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.08-2.33.154-.597.38-.97.68-1.266.21-.21.47-.363.766-.455.08-.025.16-.04.24-.05.02-.004.04-.006.06-.008.01-.001.02-.002.03-.002h.01c.01 0 .02.001.03.002.02.002.04.004.06.008.08.01.16.025.24.05.296.092.556.245.766.455.3.296.526.669.68 1.266z"></path>
                                    </svg>
                                    <span class="block mt-2">Chưa có học viên nào được ghi danh</span>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Đơn vị pháp nhân/trả lương</label>
                            <select wire:model="newHocVien.don_vi_phap_nhan_id" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn đơn vị pháp nhân --</option>
                                @foreach(\App\Models\DonViPhapNhan::all() as $dvpn)
                                    <option value="{{ $dvpn->ma_so_thue }}">{{ $dvpn->ten_don_vi }}</option>
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
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gửi Email Hàng Loạt</h3>
                <form wire:submit.prevent="guiEmailHangLoat">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chọn Mẫu Email <span class="text-red-500">*</span></label>
                            <select wire:model="selectedEmailTemplateId" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn mẫu --</option>
                                @foreach(\App\Models\EmailTemplate::all() as $template)
                                    <option value="{{ $template->id }}">{{ $template->ten_mau }}</option>
                                @endforeach
                            </select>
                            @error('selectedEmailTemplateId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chọn Tài Khoản Gửi Email <span class="text-red-500">*</span></label>
                            <select wire:model="selectedEmailAccountId" class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Chọn tài khoản --</option>
                                @foreach(\App\Models\EmailAccount::where('active', 1)->get() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->email }})</option>
                                @endforeach
                            </select>
                            @error('selectedEmailAccountId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6 text-sm text-gray-500">
                        <p>Số học viên sẽ nhận email: <span class="font-semibold">{{ $hocViensDaDangKy->count() }}</span></p>
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
