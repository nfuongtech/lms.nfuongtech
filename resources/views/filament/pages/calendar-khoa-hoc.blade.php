<x-filament::page>
    <h2 class="text-xl font-bold mb-4">Lịch học khóa: {{ $record->ma_khoa_hoc }}</h2>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2">Chuyên đề</th>
                <th class="border border-gray-300 p-2">Ngày học</th>
                <th class="border border-gray-300 p-2">Giờ bắt đầu</th>
                <th class="border border-gray-300 p-2">Giờ kết thúc</th>
                <th class="border border-gray-300 p-2">Giảng viên</th>
                <th class="border border-gray-300 p-2">Địa điểm</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->lichHocs as $lich)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $lich->chuyenDe->ten_chuyen_de ?? '' }}</td>
                    <td class="border border-gray-300 p-2">{{ $lich->ngay_hoc->format('d/m/Y') }}</td>
                    <td class="border border-gray-300 p-2">{{ $lich->gio_bat_dau }}</td>
                    <td class="border border-gray-300 p-2">{{ $lich->gio_ket_thuc }}</td>
                    <td class="border border-gray-300 p-2">{{ $lich->giangVien->ho_ten ?? '' }}</td>
                    <td class="border border-gray-300 p-2">{{ $lich->dia_diem }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-filament::page>
