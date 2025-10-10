<x-filament::page>
    <div class="space-y-6">
        <div class="flex space-x-4">
            <div class="w-1/2">
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model="selectedKhoaHoc" class="w-full fi-input">
                    <option value="">-- Chọn khóa học --</option>
                    @foreach(\App\Models\KhoaHoc::all() as $kh)
                        <option value="{{ $kh->id }}">
                            {{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-1/2">
                <label class="block text-sm font-medium text-gray-700">Buổi học</label>
                <select wire:model="selectedLichHoc" class="w-full fi-input" @if(!$selectedKhoaHoc) disabled @endif>
                    <option value="">-- Chọn buổi học --</option>
                    @if($selectedKhoaHoc)
                        @foreach(\App\Models\LichHoc::where('khoa_hoc_id', $selectedKhoaHoc)->get() as $lh)
                            <option value="{{ $lh->id }}">
                                {{ $lh->chuyenDe->ten_chuyen_de ?? '' }} - 
                                {{ $lh->ngay_hoc }} ({{ $lh->gio_bat_dau }} - {{ $lh->gio_ket_thuc }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        @if($selectedLichHoc && count($hocViens) > 0)
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Điểm danh học viên</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MSNV</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Họ tên</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lý do vắng</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Điểm buổi học</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($hocViens as $hv)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $hv['msnv'] }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $hv['ho_ten'] }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <select wire:model="diemDanhData.{{ $hv['dang_ky_id'] }}.trang_thai" class="fi-input">
                                            <option value="co_mat">Có mặt</option>
                                            <option value="vang_phep">Vắng có phép</option>
                                            <option value="vang_khong_phep">Vắng không phép</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <input type="text" wire:model="diemDanhData.{{ $hv['dang_ky_id'] }}.ly_do_vang" class="fi-input" placeholder="Lý do" />
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <input type="number" step="0.1" min="0" max="10" wire:model="diemDanhData.{{ $hv['dang_ky_id'] }}.diem_buoi_hoc" class="fi-input" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button wire:click="luuDiemDanh" class="fi-btn fi-btn-primary">
                        Lưu điểm danh
                    </button>
                </div>
            </div>
        @elseif($selectedLichHoc)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-700">Không có học viên nào đăng ký khóa học này.</p>
            </div>
        @endif
    </div>
</x-filament::page>
