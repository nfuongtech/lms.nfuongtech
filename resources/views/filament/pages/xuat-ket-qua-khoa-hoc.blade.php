<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="export">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Khóa học</label>
                    <select wire:model="filters.khoa_hoc_id" class="w-full fi-input">
                        <option value="">-- Tất cả --</option>
                        @foreach(\App\Models\KhoaHoc::all() as $kh)
                            <option value="{{ $kh->id }}">{{ $kh->ma_khoa_hoc }} - {{ $kh->chuongTrinh->ten_chuong_trinh ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kết quả</label>
                    <select wire:model="filters.ket_qua" class="w-full fi-input">
                        <option value="">-- Tất cả --</option>
                        <option value="hoan_thanh">Hoàn thành</option>
                        <option value="khong_hoan_thanh">Không hoàn thành</option>
                        <option value="dat_yeu_cau">Đạt yêu cầu</option>
                        <option value="khong_dat_yeu_cau">Không đạt yêu cầu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Trạng thái học viên</label>
                    <select wire:model="filters.trang_thai_hoc_vien" class="w-full fi-input">
                        <option value="">-- Tất cả --</option>
                        <option value="hoan_thanh">Hoàn thành</option>
                        <option value="khong_hoan_thanh">Không hoàn thành</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="fi-btn fi-btn-primary">
                    Xuất Excel
                </button>
            </div>
        </form>
    </div>
</x-filament::page>
