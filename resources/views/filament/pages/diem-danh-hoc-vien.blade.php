<x-filament::page>
    <div class="space-y-6">
        <div class="flex space-x-4">
            <div class="w-1/2">
                <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                <select wire:model="selectedKhoaHoc" class="w-full fi-input">
                    <option value="">-- Chọn khóa học --</option>
                    @foreach(\App\Models\KhoaHoc::all() as $kh)
                        <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</option>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MSNV</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ tên</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do vắng</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Điểm buổi học</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($hocViens as $hocVien)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hocVien->msnv }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hocVien->ho_ten }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <select wire:model="diemDanhData.{{ $hocVien->dangKies->firstWhere('khoa_hoc_id', $selectedKhoaHoc)->id }}.trang_thai" class="fi-input">
                                            <option value="co_mat">Có mặt</option>
                                            <option value="vang_phep">Vắng phép</option>
                                            <option value="vang_khong_phep">Vắng không phép</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="text" wire:model="diemDanhData.{{ $hocVien->dangKies->firstWhere('khoa_hoc_id', $selectedKhoaHoc)->id }}.ly_do_vang" class="fi-input" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="number" step="0.1" min="0" max="10" wire:model="diemDanhData.{{ $hocVien->dangKies->firstWhere('khoa_hoc_id', $selectedKhoaHoc)->id }}.diem_buoi_hoc" class="fi-input" />
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
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Không có học viên nào đăng ký khóa học này.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
