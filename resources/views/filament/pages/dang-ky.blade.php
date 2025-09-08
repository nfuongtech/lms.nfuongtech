<x-filament::page>
    <div class="space-y-6">

        {{-- Bộ lọc --}}
        <div class="flex items-center space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model="selectedKhoaHoc" class="fi-input w-64">
                    <option value="">-- Chọn --</option>
                    @foreach(\App\Models\KhoaHoc::all() as $kh)
                        <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select wire:model="filterTrangThai" class="fi-input w-40">
                    <option value="">Tất cả</option>
                    <option value="KE_HOACH">Kế hoạch</option>
                    <option value="BAN_HANH">Ban hành</option>
                    <option value="DANG_DAO_TAO">Đang đào tạo</option>
                    <option value="TAM_HOAN">Tạm hoãn</option>
                </select>
            </div>
        </div>

        {{-- Ô nhập MSNV --}}
        <div class="bg-white shadow rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700">Nhập MSNV (phân tách dấu phẩy)</label>
            <div class="flex space-x-2 mt-2">
                <input type="text" wire:model.lazy="msnvInput" class="fi-input w-full"
                       placeholder="VD: HV01, HV02, HV03">
                <button wire:click="store"
                        class="fi-btn fi-btn-primary">
                    Ghi danh
                </button>
            </div>

            {{-- Học viên tìm thấy --}}
            @if($parsedHocViens)
                <div class="mt-3 text-sm text-green-600">
                    <strong>Danh sách tìm thấy:</strong>
                    <ul>
                        @foreach($parsedHocViens as $hv)
                            <li>{{ $hv['display'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- MSNV không tìm thấy --}}
            @if($parsedMsnvNotFound)
                <div class="mt-3 text-sm text-red-600">
                    <strong>MSNV không tồn tại:</strong>
                    <ul>
                        @foreach($parsedMsnvNotFound as $msnv)
                            <li class="flex items-center space-x-2">
                                <span>{{ $msnv }}</span>
                                <button class="fi-btn fi-btn-xs fi-btn-primary"
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
                $kh = \App\Models\KhoaHoc::with('chuongTrinh')->find($selectedKhoaHoc);
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
                            <td class="px-2 py-1">{{ $hocViensDaDangKy->count() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Block 2: Học viên đã ghi danh --}}
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-3">Danh sách học viên đã ghi danh</h3>
            <table class="min-w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-2 py-1">MSNV</th>
                        <th class="px-2 py-1">Họ & Tên</th>
                        <th class="px-2 py-1">Chức vụ</th>
                        <th class="px-2 py-1">Đơn vị</th>
                        <th class="px-2 py-1">Khóa học</th>
                        <th class="px-2 py-1">Tên Chương trình</th>
                        <th class="px-2 py-1">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hocViensDaDangKy as $dk)
                        <tr>
                            <td class="px-2 py-1">{{ $dk->hocVien->msnv }}</td>
                            <td class="px-2 py-1">{{ $dk->hocVien->ho_ten }}</td>
                            <td class="px-2 py-1">{{ $dk->hocVien->chuc_vu }}</td>
                            <td class="px-2 py-1">{{ $dk->hocVien->donVi->ten_hien_thi ?? '' }}</td>
                            <td class="px-2 py-1">{{ $dk->khoaHoc->ma_khoa_hoc }}</td>
                            <td class="px-2 py-1">{{ $dk->khoaHoc->chuongTrinh->ten_chuong_trinh ?? '' }}</td>
                            <td class="px-2 py-1">
                                <button wire:click="deleteDangKy({{ $dk->id }})"
                                        class="fi-btn fi-btn-danger fi-btn-sm">
                                    Xóa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 py-2 text-center text-gray-500">
                                Chưa có học viên nào được ghi danh
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal thêm học viên mới --}}
    @if($showAddHocVienModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold mb-4">Thêm học viên mới</h3>
                <form wire:submit.prevent="saveNewHocVien">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">MSNV</label>
                            <input type="text" wire:model="newHocVien.msnv" placeholder="MSNV" class="fi-input w-full" readonly>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Họ và tên</label>
                            <input type="text" wire:model="newHocVien.ho_ten" placeholder="Họ và tên" class="fi-input w-full">
                            @error('newHocVien.ho_ten') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="newHocVien.email" placeholder="Email" class="fi-input w-full">
                            @error('newHocVien.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Chức vụ</label>
                            <input type="text" wire:model="newHocVien.chuc_vu" placeholder="Chức vụ" class="fi-input w-full">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Đơn vị</label>
                            <select wire:model="newHocVien.don_vi_id" class="fi-input w-full">
                                <option value="">-- Chọn đơn vị --</option>
                                @foreach(\App\Models\DonVi::all() as $dv)
                                    <option value="{{ $dv->id }}">{{ $dv->ten_hien_thi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" class="fi-btn fi-btn-secondary" wire:click="$set('showAddHocVienModal', false)">Hủy</button>
                        <button type="submit" class="fi-btn fi-btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament::page>
