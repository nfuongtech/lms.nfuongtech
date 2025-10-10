<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\KhoaHoc;
use App\Models\GiangVien;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ListKhoaHocs extends ListRecords
{
    protected static string $resource = KhoaHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Xuất Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->outlined()
                ->extraAttributes([
                    'class' => 'text-black',
                    'style' => 'color:#111827;background:#fff;border-color:#d1d5db;',
                ])
                ->action(function () {
                    // 1) LẤY QUERY ĐÃ ÁP DỤNG BỘ LỌC ĐANG KÍCH HOẠT
                    $query = null;

                    // Filament v3 có sẵn hàm này
                    if (method_exists($this, 'getFilteredTableQuery')) {
                        $query = $this->getFilteredTableQuery();
                    }

                    // Fallback nếu môi trường không có (áp thủ công theo state form filter)
                    if (! $query) {
                        $state = [];
                        if (method_exists($this, 'getTableFiltersForm')) {
                            $state = (array) $this->getTableFiltersForm()->getState();
                        }
                        $year  = (int) ($state['nam']   ?? 0);
                        $month = (int) ($state['thang'] ?? 0);
                        $week  = (int) ($state['tuan']  ?? 0);

                        $query = KhoaHoc::query()->with('lichHocs')->orderByDesc('id');
                        if ($year)  $query->where('nam', $year);
                        if ($month) $query->whereHas('lichHocs', fn($r) => $r->where('thang', $month));
                        if ($week)  $query->whereHas('lichHocs', fn($r) => $r->where('tuan',  $week));
                    }

                    $rows = $query->with('lichHocs')->get();

                    // 2) XÁC ĐỊNH CỘT ĐANG HIỂN THỊ; fallback bộ mặc định
                    $defaultKeys = ['ma_khoa_hoc','ten_khoa_hoc','giang_vien_text','tong_gio','ngay_gio','dia_diem','tuan','trang_thai_hien_thi'];
                    $exportKeys = $defaultKeys;

                    try {
                        if (method_exists($this, 'getTable')) {
                            $columns = collect($this->getTable()->getColumns());
                            $visible = $columns->filter(fn ($c) => method_exists($c, 'isVisible') ? $c->isVisible() : true)
                                               ->map(fn ($c) => method_exists($c, 'getName') ? $c->getName() : null)
                                               ->filter()
                                               ->values()
                                               ->all();
                            // Lọc theo thứ tự mặc định
                            $exportKeys = array_values(array_intersect($defaultKeys, $visible));
                            if (empty($exportKeys)) $exportKeys = $defaultKeys;
                        }
                    } catch (\Throwable $e) {
                        // dùng mặc định nếu không lấy được
                        $exportKeys = $defaultKeys;
                    }

                    // 3) ĐỊNH NGHĨA LABEL + CÁCH LẤY GIÁ TRỊ TỪ RECORD
                    $defs = [
                        'ma_khoa_hoc' => ['Mã KH', fn (KhoaHoc $kh) => (string) $kh->ma_khoa_hoc],
                        'ten_khoa_hoc'=> ['Tên khóa học', fn (KhoaHoc $kh) => (string) $kh->ten_khoa_hoc],
                        'giang_vien_text' => ['Giảng viên', function (KhoaHoc $kh) {
                            $ids = $kh->lichHocs->pluck('giang_vien_id')->filter()->unique()->values();
                            return $ids->isEmpty()
                                ? ''
                                : GiangVien::whereIn('id', $ids)->pluck('ho_ten')->filter()->implode(', ');
                        }],
                        'tong_gio' => ['Tổng giờ', fn (KhoaHoc $kh) => (string) number_format((float) ($kh->lichHocs->sum('so_gio_giang') ?? 0), 1)],
                        'ngay_gio' => ['Ngày/Giờ đào tạo', function (KhoaHoc $kh) {
                            return $kh->lichHocs->sortBy('ngay_hoc')->map(function ($lh) {
                                $date  = Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                                $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                                $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                                return "{$date}, {$start}-{$end}";
                            })->implode("\n");
                        }],
                        'dia_diem' => ['Địa điểm', function (KhoaHoc $kh) {
                            return $kh->lichHocs->load('diaDiem')->map(
                                fn ($lh) => $lh->dia_diem ?: optional($lh->diaDiem)->ten_phong
                            )->filter()->unique()->implode(', ');
                        }],
                        'tuan' => ['Tuần', fn (KhoaHoc $kh) => $kh->lichHocs->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ')],
                        'trang_thai_hien_thi' => ['Trạng thái', fn (KhoaHoc $kh) => (string) $kh->computeTrangThai()],
                    ];

                    // 4) XUẤT XLSX
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // Header
                    foreach ($exportKeys as $idx => $key) {
                        $sheet->setCellValueByColumnAndRow($idx+1, 1, $defs[$key][0]);
                        $sheet->getStyleByColumnAndRow($idx+1, 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Rows
                    $r = 2;
                    foreach ($rows as $kh) {
                        foreach ($exportKeys as $c => $key) {
                            $value = $defs[$key][1]($kh);
                            $sheet->setCellValueByColumnAndRow($c+1, $r, $value);
                        }
                        // Wrap text cho cột Ngày/Giờ
                        $colIndex = array_search('ngay_gio', $exportKeys, true);
                        if ($colIndex !== false) {
                            $sheet->getStyleByColumnAndRow($colIndex+1, $r)->getAlignment()->setWrapText(true);
                        }
                        $r++;

                        // Đồng bộ trạng thái
                        $kh->syncTrangThai();
                    }

                    // Autosize
                    foreach (range(1, count($exportKeys)) as $idx) {
                        $sheet->getColumnDimensionByColumn($idx)->setAutoSize(true);
                    }

                    $fileName = 'ke_hoach_' . now()->format('Ymd_His') . '.xlsx';
                    $writer = new XlsxWriter($spreadsheet);

                    return new StreamedResponse(function () use ($writer) {
                        $writer->save('php://output');
                    }, 200, [
                        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => "attachment; filename=\"$fileName\"",
                        'Cache-Control'       => 'max-age=0',
                    ]);
                }),

            Actions\CreateAction::make()->label('Tạo Kế hoạch'),
        ];
    }
}
