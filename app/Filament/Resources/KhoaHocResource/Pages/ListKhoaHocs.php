<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Support\Carbon;

class ListKhoaHocs extends ListRecords
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Kế hoạch đào tạo';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_all')
                ->label('Xuất Excel')
                ->extraAttributes([
                    'style' => 'background-color:#CCFFD8;color:#111827;border:1px solid #9ae6b4;',
                    'class' => 'text-gray-900',
                ])
                ->action(fn () => $this->exportPlan()),
            Actions\CreateAction::make()
                ->label('Tạo kế hoạch')
                ->extraAttributes([
                    'style' => 'background-color:#FFFCD5;color:#00529C;border:1px solid #e5d89f;',
                ]),
        ];
    }

    public function exportPlan()
    {
        $records = $this->getFilteredTableQuery()
            ->with(['lichHocs.giangVien' => fn ($q) => $q->select(['id','ho_ten'])])
            ->get();

        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = ['TT','Mã khóa','Tên khóa học','Giảng viên','Tổng giờ','Ngày, Giờ đào tạo','Tuần','Trạng thái','Lý do tạm hoãn'];
            $sheet->fromArray($headers, null, 'A1');

            $row = 2; $tt = 1;

            foreach ($records as $kh) {
                $names = $kh->lichHocs
                    ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $gv = !empty($names) ? implode(', ', $names) : '';

                $tong = number_format((float) $kh->lichHocs->sum('so_gio_giang'), 1, '.', '');

                $lich = $kh->lichHocs->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']])->values();
                $ngayGioLines = $lich->map(function ($lh) {
                    $date  = \Carbon\Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                    $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                    $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                    return "{$date}, {$start}-{$end}";
                })->all();
                $ngayGio = implode("\n", $ngayGioLines);

                $weeks = $lich->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');

                $trangThai = $kh->trang_thai_hien_thi;
                $lyDoTamHoan = $trangThai === 'Tạm hoãn' ? (string) ($kh->ly_do_tam_hoan ?? '') : '';

                $sheet->fromArray([
                    $tt,
                    $kh->ma_khoa_hoc,
                    $kh->ten_khoa_hoc,
                    $gv,
                    $tong,
                    $ngayGio,
                    $weeks,
                    $trangThai,
                    $lyDoTamHoan,
                ], null, 'A'.$row);

                $row++; $tt++;
            }

            $sheet->getStyle('D1:D'.($row-1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('F1:F'.($row-1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('I1:I'.($row-1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);

            foreach (range('A','I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'ke_hoach_dao_tao_'.now()->format('Ymd_His').'.xlsx';
            $temp = storage_path("app/$filename");
            $writer->save($temp);
            return response()->download($temp)->deleteFileAfterSend(true);
        }

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ke_hoach_dao_tao_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['TT','Mã khóa','Tên khóa học','Giảng viên','Tổng giờ','Ngày, Giờ đào tạo','Tuần','Trạng thái','Lý do tạm hoãn']);

            $tt = 1;
            foreach ($records as $kh) {
                $names = $kh->lichHocs
                    ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $gv = !empty($names) ? implode(', ', $names) : '';

                $tong = number_format((float) $kh->lichHocs->sum('so_gio_giang'), 1, '.', '');

                $lich = $kh->lichHocs->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']])->values();
                $ngayGio = $lich->map(function ($lh) {
                    $date  = \Carbon\Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                    $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                    $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                    return "{$date}, {$start}-{$end}";
                })->implode(' | ');

                $weeks = $lich->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');

                $trangThai = $kh->trang_thai_hien_thi;
                $lyDoTamHoan = $trangThai === 'Tạm hoãn' ? (string) ($kh->ly_do_tam_hoan ?? '') : '';

                fputcsv($out, [
                    $tt,
                    $kh->ma_khoa_hoc,
                    $kh->ten_khoa_hoc,
                    $gv,
                    $tong,
                    $ngayGio,
                    $weeks,
                    $trangThai,
                    $lyDoTamHoan,
                ]);

                $tt++;
            }

            fclose($out);
        }, null, $headers);
    }

    protected function getDefaultTableFilters(): array
    {
        $defaults = parent::getDefaultTableFilters();
        $now = Carbon::now();

        $defaults['thoi_gian'] = array_merge([
            'isEnabled' => true,
            'data' => [],
        ], $defaults['thoi_gian'] ?? []);

        $defaults['thoi_gian']['data']['nam'] ??= (int) $now->format('Y');
        $defaults['thoi_gian']['data']['thang'] ??= (int) $now->format('n');

        return $defaults;
    }
}
