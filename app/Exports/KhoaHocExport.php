<?php

namespace App\Exports;

use App\Models\KhoaHoc;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KhoaHocExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'TT',
            'Mã KH',
            'Tên Khóa học',
            'Trạng thái',
            'Số buổi',
            'Tuần',
            'Tháng/Năm',
            'Lịch học',
            'Giảng viên',
        ];
    }

    public function map($record): array
    {
        static $index = 0;
        $index++;

        // Trạng thái thực tế
        $trang_thai = $record->trang_thai;
        $lichs = $record->lichHocs()->orderBy('ngay_hoc')->get();
        if ($lichs->isNotEmpty()) {
            $first = Carbon::parse($lichs->first()->ngay_hoc);
            $last  = Carbon::parse($lichs->last()->ngay_hoc);
            $today = Carbon::today();
            if ($last->lt($today)) {
                $trang_thai = 'Kết thúc';
            } elseif ($first->lte($today) && $last->gte($today)) {
                $trang_thai = 'Đang đào tạo';
            }
        }

        return [
            $index,
            $record->ma_khoa_hoc,
            ($record->ten_khoa_hoc ?? '') ?: (($record->chuongTrinh?->ten_chuong_trinh ?? 'N/A') . ', ' . $record->ma_khoa_hoc),
            $trang_thai,
            $record->lichHocs()->count(),
            $record->lichHocs->pluck('tuan')->unique()->join(', '),
            $record->lichHocs->isNotEmpty()
                ? $record->lichHocs->first()->thang . '/' . $record->lichHocs->first()->nam
                : '',
            $record->lichHocs->sortByDesc('ngay_hoc')->map(function ($l) {
                $ng = $l->ngay_hoc ? Carbon::parse($l->ngay_hoc)->format('d/m/Y') : '';
                $gbd = substr($l->gio_bat_dau, 0, 5);
                $gkt = substr($l->gio_ket_thuc, 0, 5);
                return trim($ng . " ({$gbd}-{$gkt})");
            })->filter()->join('; '),
            $record->lichHocs->map(fn ($l) => $l->giangVien?->ho_ten)->unique()->filter()->join('; '),
        ];
    }
}
