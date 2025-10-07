<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListKhoaHocs extends ListRecords
{
    protected static string $resource = KhoaHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_all')
                ->label('Xuất kế hoạch')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportPlan()),
            Actions\CreateAction::make()->label('Tạo kế hoạch'),
        ];
    }

    public function exportPlan()
    {
        // Tôn trọng bộ lọc hiện hành
        $records = $this->getFilteredTableQuery()
            ->with(['lichHocs.giangVien' => fn ($q) => $q->select(['id','ho_ten'])])
            ->get();

        // ===== Preferred: Excel (PhpSpreadsheet) =====
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $headers = ['TT','Mã khóa','Tên khóa học','Giảng viên','Tổng giờ','Ngày, Giờ đào tạo','Tuần','Trạng thái'];
            $sheet->fromArray($headers, null, 'A1');

            $row = 2; $tt = 1;

            foreach ($records as $kh) {
                // Giảng viên: chỉ tên, unique, nối bằng dấu phẩy
                $names = $kh->lichHocs
                    ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $gv = !empty($names) ? implode(', ', $names) : '';

                // Tổng giờ
                $tong = (int) $kh->lichHocs->sum('so_gio_giang');

                // Ngày, Giờ đào tạo: tất cả lịch, mỗi lịch 1 dòng
                $lich = $kh->lichHocs->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']])->values();
                $ngayGioLines = $lich->map(function ($lh) {
                    $date  = \Carbon\Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                    $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                    $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                    return "{$date}, {$start}-{$end}";
                })->all();
                $ngayGio = implode("\n", $ngayGioLines);

                // Tuần: unique desc
                $weeks = $lich->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');

                // Trạng thái: cùng logic như List
                $qs = $kh->lichHocs()->select('ngay_hoc','gio_bat_dau','gio_ket_thuc');
                if (!$qs->exists()) {
                    $trangThai = 'Dự thảo';
                } else {
                    $all = $qs->get()->map(function ($lh) {
                        $day = $lh->ngay_hoc instanceof \DateTimeInterface
                            ? \Carbon\Carbon::instance($lh->ngay_hoc)->startOfDay()
                            : \Carbon\Carbon::parse($lh->ngay_hoc)->startOfDay();
                        $start = (clone $day)->setTimeFromTimeString($lh->gio_bat_dau ?: '00:00:00');
                        $end   = (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc ?: '23:59:59');
                        return compact('start','end');
                    });
                    $minStart = $all->min('start');
                    $maxEnd   = $all->max('end');
                    $now = now();

                    if ($now->lt($minStart))      $trangThai = 'Ban hành';
                    elseif ($now->between($minStart, $maxEnd)) $trangThai = 'Đang đào tạo';
                    else                           $trangThai = 'Kết thúc';
                }

                $sheet->fromArray([
                    $tt,
                    $kh->ma_khoa_hoc,
                    $kh->ten_khoa_hoc,
                    $gv,
                    $tong,
                    $ngayGio,
                    $weeks,
                    $trangThai,
                ], null, 'A'.$row);

                $row++; $tt++;
            }

            // Style: wrap text cột Giảng viên (D) & Ngày, Giờ đào tạo (F)
            $sheet->getStyle('D1:D'.($row-1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('F1:F'.($row-1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:H1')->getFont()->setBold(true);

            // Auto width
            foreach (range('A','H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'ke_hoach_dao_tao_'.now()->format('Ymd_His').'.xlsx';
            $temp = storage_path("app/$filename");
            $writer->save($temp);
            return response()->download($temp)->deleteFileAfterSend(true);
        }

        // ===== Fallback: CSV =====
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ke_hoach_dao_tao_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['TT','Mã khóa','Tên khóa học','Giảng viên','Tổng giờ','Ngày, Giờ đào tạo','Tuần','Trạng thái']);

            $tt = 1;
            foreach ($records as $kh) {
                $names = $kh->lichHocs
                    ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $gv = !empty($names) ? implode(', ', $names) : '';

                $tong = (int) $kh->lichHocs->sum('so_gio_giang');

                $lich = $kh->lichHocs->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']])->values();
                $ngayGio = $lich->map(function ($lh) {
                    $date  = \Carbon\Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                    $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                    $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                    return "{$date}, {$start}-{$end}";
                })->implode(' | '); // CSV không xuống dòng trong ô

                $weeks = $lich->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');

                $qs = $kh->lichHocs()->select('ngay_hoc','gio_bat_dau','gio_ket_thuc');
                if (!$qs->exists()) {
                    $trangThai = 'Dự thảo';
                } else {
                    $all = $qs->get()->map(function ($lh) {
                        $day = $lh->ngay_hoc instanceof \DateTimeInterface
                            ? \Carbon\Carbon::instance($lh->ngay_hoc)->startOfDay()
                            : \Carbon\Carbon::parse($lh->ngay_hoc)->startOfDay();
                        $start = (clone $day)->setTimeFromTimeString($lh->gio_bat_dau ?: '00:00:00');
                        $end   = (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc ?: '23:59:59');
                        return compact('start','end');
                    });
                    $minStart = $all->min('start');
                    $maxEnd   = $all->max('end');
                    $now = now();

                    if ($now->lt($minStart))      $trangThai = 'Ban hành';
                    elseif ($now->between($minStart, $maxEnd)) $trangThai = 'Đang đào tạo';
                    else                           $trangThai = 'Kết thúc';
                }

                fputcsv($out, [
                    $tt,
                    $kh->ma_khoa_hoc,
                    $kh->ten_khoa_hoc,
                    $gv,
                    $tong,
                    $ngayGio,
                    $weeks,
                    $trangThai,
                ]);

                $tt++;
            }

            fclose($out);
        }, null, $headers);
    }
}
