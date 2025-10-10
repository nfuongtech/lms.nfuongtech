<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\KhoaHoc;
use App\Models\GiangVien;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Notifications\Notification;

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
            // Xuất Excel theo bộ lọc hiện hành & cột mặc định của bảng
            Actions\Action::make('export_xlsx')
                ->label('Xuất excel')
                ->color('gray')
                ->action(fn () => $this->exportXlsx()),
            Actions\CreateAction::make()->label('Tạo kế hoạch'),
        ];
    }

    /**
     * Filters: Năm & Tháng (1 hàng), Tuần, Ngày đào tạo, Trạng thái (multiple).
     */
    protected function getTableFilters(): array
    {
        $yearOptions = KhoaHoc::query()
            ->select('nam')->distinct()->orderByDesc('nam')->pluck('nam', 'nam')->toArray();

        $monthOptions = DB::table('lich_hocs')
            ->select('thang')->distinct()->orderBy('thang')
            ->pluck('thang', 'thang')->filter()->toArray();

        $weekOptions = DB::table('lich_hocs')
            ->select('tuan')->distinct()->orderBy('tuan')
            ->pluck('tuan', 'tuan')->filter()->toArray();

        return [
            Tables\Filters\Filter::make('nam_thang_group')
                ->label('Thời gian')
                ->form([
                    \Filament\Forms\Components\Grid::make(2)->schema([
                        \Filament\Forms\Components\Select::make('nam')
                            ->label('Năm')
                            ->options($yearOptions)
                            ->native(false),
                        \Filament\Forms\Components\Select::make('thang')
                            ->label('Tháng')
                            ->options($monthOptions)
                            ->native(false),
                    ]),
                ])
                ->query(function (Builder $q, array $data): Builder {
                    if (!empty($data['nam'])) {
                        $q->where('nam', (int) $data['nam']);
                    }
                    if (!empty($data['thang'])) {
                        $q->whereHas('lichHocs', fn ($r) => $r->where('thang', (int) $data['thang']));
                    }
                    return $q;
                }),

            Tables\Filters\SelectFilter::make('tuan')
                ->label('Tuần')
                ->options($weekOptions)
                ->native(false)
                ->query(fn (Builder $q, array $data) =>
                    filled($data['value'] ?? null)
                        ? $q->whereHas('lichHocs', fn ($r) => $r->where('tuan', (int) $data['value']))
                        : $q
                ),

            Tables\Filters\Filter::make('ngay')
                ->label('Ngày đào tạo')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('ngay')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ])
                ->query(function (Builder $q, array $data): Builder {
                    if (empty($data['ngay'])) return $q;
                    $date = $data['ngay'] instanceof \DateTimeInterface
                        ? $data['ngay']->format('Y-m-d')
                        : (string) $data['ngay'];
                    return $q->whereHas('lichHocs', fn ($r) => $r->whereDate('ngay_hoc', $date));
                }),

            Tables\Filters\SelectFilter::make('trang_thais')
                ->label('Trạng thái')
                ->multiple()
                ->options([
                    'Dự thảo'      => 'Dự thảo',
                    'Ban hành'     => 'Ban hành',
                    'Đang đào tạo' => 'Đang đào tạo',
                    'Kết thúc'     => 'Kết thúc',
                    'Tạm hoãn'     => 'Tạm hoãn',
                ])
                ->native(false)
                ->query(function (Builder $q, array $data): Builder {
                    $values = collect($data['values'] ?? [])->filter()->values();
                    if ($values->isEmpty()) return $q;

                    $now = now()->format('Y-m-d H:i:s');

                    $idsTamHoan  = KhoaHoc::query()->where('tam_hoan', true)->pluck('id');
                    $idsDuThao   = KhoaHoc::query()->doesntHave('lichHocs')->pluck('id');

                    $range = DB::table('lich_hocs')
                        ->selectRaw('khoa_hoc_id, MIN(CONCAT(ngay_hoc, " ", COALESCE(gio_bat_dau,"00:00:00"))) as start_at, MAX(CONCAT(ngay_hoc, " ", COALESCE(gio_ket_thuc,"23:59:59"))) as end_at')
                        ->groupBy('khoa_hoc_id');

                    $idsBanHanh = DB::query()->fromSub($range, 'r')
                        ->where('r.start_at', '>', $now)->pluck('khoa_hoc_id');

                    $idsDangDaoTao = DB::query()->fromSub($range, 'r')
                        ->where('r.start_at', '<=', $now)
                        ->where('r.end_at', '>=', $now)
                        ->pluck('khoa_hoc_id');

                    $idsKetThuc = DB::query()->fromSub($range, 'r')
                        ->where('r.end_at', '<', $now)->pluck('khoa_hoc_id');

                    $needIds = collect();
                    foreach ($values as $st) {
                        $needIds = match ($st) {
                            'Tạm hoãn'     => $needIds->merge($idsTamHoan),
                            'Dự thảo'      => $needIds->merge($idsDuThao),
                            'Ban hành'     => $needIds->merge($idsBanHanh),
                            'Đang đào tạo' => $needIds->merge($idsDangDaoTao),
                            'Kết thúc'     => $needIds->merge($idsKetThuc),
                            default        => $needIds,
                        };
                    }

                    $ids = $needIds->unique()->values();
                    if ($ids->isEmpty()) {
                        return $q->whereRaw('0=1');
                    }
                    return $q->whereIn('id', $ids);
                }),
        ];
    }

    /**
     * Xuất XLSX theo bộ lọc hiện hành & theo các cột mặc định của bảng.
     */
    protected function exportXlsx()
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            Notification::make()->title('Thiếu thư viện phpoffice/phpspreadsheet')->danger()->send();
            return null;
        }

        $states = $this->getTableFiltersForm()->getState() ?? [];
        $query  = KhoaHocResource::getEloquentQuery();

        // Áp dụng lại như Filters
        $grp = $states['nam_thang_group'] ?? [];
        if (!empty($grp['nam'])) {
            $query->where('nam', (int) $grp['nam']);
        }
        if (!empty($grp['thang'])) {
            $query->whereHas('lichHocs', fn ($r) => $r->where('thang', (int) $grp['thang']));
        }

        if (!empty($states['tuan']['value'])) {
            $query->whereHas('lichHocs', fn ($r) => $r->where('tuan', (int) $states['tuan']['value']));
        }

        if (!empty($states['ngay']['ngay'])) {
            $date = $states['ngay']['ngay'] instanceof \DateTimeInterface
                ? $states['ngay']['ngay']->format('Y-m-d')
                : (string) $states['ngay']['ngay'];
            $query->whereHas('lichHocs', fn ($r) => $r->whereDate('ngay_hoc', $date));
        }

        if (!empty($states['trang_thais']['values'])) {
            $now = now()->format('Y-m-d H:i:s');

            $idsTamHoan  = KhoaHoc::query()->where('tam_hoan', true)->pluck('id');
            $idsDuThao   = KhoaHoc::query()->doesntHave('lichHocs')->pluck('id');

            $range = \DB::table('lich_hocs')
                ->selectRaw('khoa_hoc_id, MIN(CONCAT(ngay_hoc, " ", COALESCE(gio_bat_dau,"00:00:00"))) as start_at, MAX(CONCAT(ngay_hoc, " ", COALESCE(gio_ket_thuc,"23:59:59"))) as end_at')
                ->groupBy('khoa_hoc_id');

            $idsBanHanh = \DB::query()->fromSub($range, 'r')
                ->where('r.start_at', '>', $now)->pluck('khoa_hoc_id');

            $idsDangDaoTao = \DB::query()->fromSub($range, 'r')
                ->where('r.start_at', '<=', $now)
                ->where('r.end_at', '>=', $now)
                ->pluck('khoa_hoc_id');

            $idsKetThuc = \DB::query()->fromSub($range, 'r')
                ->where('r.end_at', '<', $now)->pluck('khoa_hoc_id');

            $need = collect();
            foreach ($states['trang_thais']['values'] as $st) {
                $need = match ($st) {
                    'Tạm hoãn'     => $need->merge($idsTamHoan),
                    'Dự thảo'      => $need->merge($idsDuThao),
                    'Ban hành'     => $need->merge($idsBanHanh),
                    'Đang đào tạo' => $need->merge($idsDangDaoTao),
                    'Kết thúc'     => $need->merge($idsKetThuc),
                    default        => $need,
                };
            }
            $ids = $need->unique()->values();
            if ($ids->isEmpty()) {
                $query->whereRaw('0=1');
            } else {
                $query->whereIn('id', $ids);
            }
        }

        $records = $query->with(['lichHocs.giangVien', 'lichHocs.diaDiem'])->get();

        // Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KeHoachDaoTao');

        // Cột xuất – khớp cột mặc định của bảng
        $headers = [
            'TT', 'Mã khóa', 'Tên khóa học', 'Giảng viên', 'Tổng giờ',
            'Ngày, Giờ đào tạo', 'Địa điểm', 'Tuần', 'Trạng thái', 'Lý do tạm hoãn',
        ];
        foreach ($headers as $i => $text) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $text);
        }

        $row = 2;
        foreach ($records as $index => $record) {
            $gvIds = $record->lichHocs->pluck('giang_vien_id')->filter()->unique()->values();
            $gvText = $gvIds->isEmpty()
                ? '—'
                : GiangVien::query()->whereIn('id', $gvIds)->pluck('ho_ten')->filter()->implode(', ');

            $tongGio = (float) ($record->lichHocs->sum('so_gio_giang') ?? 0);

            $ngayGio = $record->lichHocs->sortBy('ngay_hoc')->map(function ($lh) {
                $date  = Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                return "{$date}, {$start}-{$end}";
            })->implode("\n");

            $diaDiem = $record->lichHocs->map(fn ($lh) => $lh->dia_diem ?: optional($lh->diaDiem)->ten_phong)
                ->filter()->unique()->implode(', ');

            $tuan = $record->lichHocs->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');

            // Tính trạng thái giống bảng
            $state = (function () use ($record) {
                if ($record->tam_hoan) return 'Tạm hoãn';
                $ranges = $record->lichHocs->map(function ($lh) {
                    $day = $lh->ngay_hoc instanceof \DateTimeInterface
                        ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                        : Carbon::parse($lh->ngay_hoc)->startOfDay();
                    $start = (clone $day)->setTimeFromTimeString($lh->gio_bat_dau ?: '00:00:00');
                    $end   = (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc ?: '23:59:59');
                    return compact('start','end');
                });
                if ($ranges->isEmpty()) return 'Dự thảo';
                $minStart = $ranges->min('start');
                $maxEnd   = $ranges->max('end');
                $now = now();
                if ($now->lt($minStart)) return 'Ban hành';
                if ($now->between($minStart, $maxEnd)) return 'Đang đào tạo';
                return 'Kết thúc';
            })();

            $sheet->setCellValueByColumnAndRow(1, $row, $index + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, (string) $record->ma_khoa_hoc);
            $sheet->setCellValueByColumnAndRow(3, $row, (string) $record->ten_khoa_hoc);
            $sheet->setCellValueByColumnAndRow(4, $row, (string) $gvText);
            $sheet->setCellValueByColumnAndRow(5, $row, $tongGio);
            $sheet->setCellValueByColumnAndRow(6, $row, (string) $ngayGio);
            $sheet->setCellValueByColumnAndRow(7, $row, (string) $diaDiem);
            $sheet->setCellValueByColumnAndRow(8, $row, (string) $tuan);
            $sheet->setCellValueByColumnAndRow(9, $row, (string) $state);
            $sheet->setCellValueByColumnAndRow(10, $row, (string) ($record->tam_hoan ? ($record->ly_do_tam_hoan ?? '') : ''));

            $sheet->getStyle("A{$row}:J{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        // Width
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(36);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(32);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(28);

        $fileName = 'ke_hoach_dao_tao_' . now()->format('Ymd_His') . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
