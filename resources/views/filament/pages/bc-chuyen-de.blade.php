<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Phần bộ lọc --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center space-x-4">
                <div class="w-1/5">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="filterMonth" wire:change="generateReport">
                            <option value="">-- Chọn tháng --</option>
                            @foreach($this->getMonths() as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="w-1/5">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="filterYear" wire:change="generateReport">
                            <option value="">-- Chọn năm --</option>
                            @foreach($this->getYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="filterKhoaHoc" wire:change="generateReport">
                            <option value="">-- Lọc theo Khóa học --</option>
                            @foreach($this->getKhoaHocOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <x-filament::button wire:click="export">
                        Xuất Excel
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Phần bảng báo cáo --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">TT</th>
                        <th scope="col" class="px-6 py-3">Tên Chuyên đề</th>
                        <th scope="col" class="px-6 py-3">Khóa/Lớp</th>
                        <th scope="col" class="px-6 py-3">Giảng viên</th>
                        <th scope="col" class="px-6 py-3">Thời gian đào tạo</th>
                        <th scope="col" class="px-6 py-3">Số lượng</th>
                        <th scope="col" class="px-6 py-3 text-center" colspan="3">Chuyên cần</th>
                        <th scope="col" class="px-6 py-3 text-center">Kết quả</th>
                    </tr>
                    <tr>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3"></th>
                        <th scope="col" class="px-4 py-2 text-center font-medium">Có mặt</th>
                        <th scope="col" class="px-4 py-2 text-center font-medium">Phép</th>
                        <th scope="col" class="px-4 py-2 text-center font-medium">Không phép</th>
                        <th scope="col" class="px-4 py-2 text-center font-medium">Đạt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $index => $row)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $row['ten_chuyen_de'] }}</td>
                            <td class="px-6 py-4">{{ $row['lop_khoa'] }}</td>
                            <td class="px-6 py-4">{{ $row['giang_viens'] }}</td>
                            <td class="px-6 py-4">{{ $row['thoi_gian_dao_tao'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['so_luong'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['co_mat'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['phep'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['khong_phep'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['dat_yeu_cau'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center p-4">Không có dữ liệu cho bộ lọc đã chọn.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
