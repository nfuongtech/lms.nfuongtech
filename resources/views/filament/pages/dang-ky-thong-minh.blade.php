<x-filament::page>
    <div class="space-y-6">
        {{-- Filters (horizontal) --}}
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Khóa học</label>
                <select wire:model="selectedKhoaHocId" class="block w-72 rounded border px-3 py-2">
                    <option value="">-- Tất cả khóa học --</option>
                    @foreach(\App\Models\KhoaHoc::orderByDesc('created_at')->get() as $kh)
                        <option value="{{ $kh->id }}">
                            {{ $kh->ma_khoa_hoc }} — {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }} ({{ $kh->nam }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Trạng thái khóa học</label>
                <select wire:model="selectedTrangThai" class="block w-48 rounded border px-3 py-2">
                    <option value="">-- Tất cả --</option>
                    @foreach(\App\Models\KhoaHoc::distinct()->pluck('trang_thai') as $tt)
                        <option value="{{ $tt }}">{{ $tt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tuần</label>
                <input type="number" wire:model.defer="selectedTuan" min="1" max="53" class="w-20 rounded border px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tháng</label>
                <input type="number" wire:model.defer="selectedThang" min="1" max="12" class="w-20 rounded border px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Năm</label>
                <input type="number" wire:model.defer="selectedNam" min="2000" max="2100" class="w-28 rounded border px-2 py-1">
            </div>

            <div>
                <button wire:click="refreshData" class="ml-2 inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded">Áp dụng</button>
            </div>
        </div>

        {{-- MSNV input & actions --}}
        <div class="bg-white p-4 rounded shadow-sm">
            <div class="grid grid-cols-12 gap-4 items-start">
                <div class="col-span-12 lg:col-span-8">
                    <label class="block text-sm font-medium mb-1">Nhập / Dán MSNV (phân tách bằng dấu phẩy hoặc xuống dòng)</label>
                    <textarea wire:model.lazy="msnvInput" rows="4" class="w-full rounded border px-3 py-2" placeholder="VD: HV01,HV02 hoặc HV01↵HV02"></textarea>
                </div>

                <div class="col-span-12 lg:col-span-4 flex gap-2">
                    <button wire:click="parseMsnv" class="w-1/2 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded">Phân tích</button>
                    <button wire:click="store" class="w-1/2 inline-flex items-center justify-center px-3 py-2 bg-green-600 text-white rounded">Ghi danh</button>
                </div>
            </div>

            {{-- Parsed results --}}
            <div class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-7">
                    <h4 class="font-medium mb-2">Học viên tìm thấy (đang làm việc)</h4>
                    @if(empty($parsedHocViens))
                        <div class="text-sm text-gray-500">Chưa có học viên được phân tích.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($parsedHocViens as $hv)
                                <div class="flex items-center justify-between border rounded p-2">
                                    <div>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" wire:model="selectedHocVienIds" value="{{ $hv['id'] }}">
                                            <span class="font-medium">{{ $hv['display'] }}</span>
                                        </label>
                                        <div class="text-xs text-gray-500">{{ $hv['don_vi'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-span-12 lg:col-span-5">
                    <h4 class="font-medium mb-2">MSNV không tìm thấy</h4>
                    @if(empty($parsedMsnvNotFound))
                        <div class="text-sm text-gray-500">Không có MSNV bị thiếu.</div>
                    @else
                        <ul class="space-y-2">
                            @foreach($parsedMsnvNotFound as $msnv)
                                <li class="flex items-center justify-between border rounded p-2">
                                    <div class="text-sm">{{ $msnv }}</div>
                                    <div class="flex gap-2">
                                        <button wire:click="openCreateHocVienModal('{{ $msnv }}')" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Thêm mới</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Block 1: danh sách Khóa học --}}
        <div class="bg-white p-4 rounded shadow-sm">
            <h3 class="text-lg font-semibold mb-3">Block 1 — Khóa học & số lượng học viên</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-3 py-2">Mã</th>
                            <th class="text-left px-3 py-2">Chương trình</th>
                            <th class="text-left px-3 py-2">Năm</th>
                            <th class="text-left px-3 py-2">Trạng thái</th>
                            <th class="text-right px-3 py-2">Số HV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($block1 as $kh)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $kh->ma_khoa_hoc }}</td>
                                <td class="px-3 py-2">{{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</td>
                                <td class="px-3 py-2">{{ $kh->nam }}</td>
                                <td class="px-3 py-2">{{ $kh->trang_thai }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ $kh->dang_kys_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Không có Khóa học</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Block 2: danh sách DangKy --}}
        <div class="bg-white p-4 rounded shadow-sm">
            <h3 class="text-lg font-semibold mb-3">Block 2 — Danh sách Học viên đã ghi danh</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-3 py-2">MSNV</th>
                            <th class="text-left px-3 py-2">Họ & tên</th>
                            <th class="text-left px-3 py-2">Đơn vị</th>
                            <th class="text-left px-3 py-2">Khóa học</th>
                            <th class="text-left px-3 py-2">Thời gian</th>
                            <th class="text-center px-3 py-2">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($block2 as $dk)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ optional($dk->hocVien)->msnv }}</td>
                                <td class="px-3 py-2">{{ optional($dk->hocVien)->ho_ten }}</td>
                                <td class="px-3 py-2">{{ optional($dk->hocVien->donVi)->ten_hien_thi ?? '' }}</td>
                                <td class="px-3 py-2">{{ optional($dk->khoaHoc)->ma_khoa_hoc }}</td>
                                <td class="px-3 py-2">
                                    {{-- show created_at (fallback) --}}
                                    {{ optional($dk->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button wire:click="deleteDangKy({{ $dk->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded text-sm">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Chưa có học viên ghi danh theo bộ lọc</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal: tạo nhanh Học viên (nếu mở) --}}
        @if($showCreateHocVienModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 px-4">
                <div class="bg-white rounded-lg w-full max-w-2xl p-6">
                    <h3 class="text-lg font-semibold mb-4">Thêm nhanh Học viên</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm font-medium">MSNV</label>
                            <input type="text" wire:model="newMsnv" class="w-full border rounded px-3 py-2" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Họ và tên</label>
                            <input type="text" wire:model="newHoTen" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Email</label>
                            <input type="email" wire:model="newEmail" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Đơn vị</label>
                            <select wire:model="newDonViId" class="w-full border rounded px-3 py-2">
                                <option value="">-- chọn đơn vị --</option>
                                @foreach(\App\Models\DonVi::orderBy('thaco_tdtv')->get() as $dv)
                                    <option value="{{ $dv->id }}">{{ $dv->ten_hien_thi ?? $dv->thaco_tdtv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button wire:click="$set('showCreateHocVienModal', false)" class="px-4 py-2 rounded bg-gray-300">Hủy</button>
                        <button wire:click="createHocVien" class="px-4 py-2 rounded bg-blue-600 text-white">Lưu & Thêm</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
