<?php

namespace App\Filament\Pages;

use App\Models\KetQuaKhoaHoc;
use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\KhoaHoc;
use App\Models\LichHoc;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\TextInput as FormsTextInput;
use Filament\Forms\Components\Textarea as FormsTextarea;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CapNhatKetQua extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    protected static bool $shouldRegisterNavigation = false;

    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Cập nhật kết quả học tập';
    protected static ?string $title           = 'Cập nhật kết quả học tập';
    protected static string $view             = 'filament.pages.cap-nhat-ket-qua';

    public static function getSlug(): string
    {
        return 'cap-nhat-ket-qua';
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $query = KetQuaKhoaHoc::query()
            ->with([
                'dangKy.hocVien:id,msnv,ho_ten',
                'dangKy.khoaHoc:id,ma_khoa_hoc,chuong_trinh_id',
                'dangKy.khoaHoc.chuongTrinh:id,ten_chuong_trinh',
            ])
            // CHỈ hiển thị các bản ghi đang chờ duyệt
            ->where('needs_review', true);

        // Loại các bản ghi đã được CHUYỂN sang 2 module đích
        if (Schema::hasTable('hoc_vien_hoan_thanhs')) {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('hoc_vien_hoan_thanhs as hvht')
                  ->whereColumn('hvht.ket_qua_khoa_hoc_id', 'ket_qua_khoa_hocs.id');
            });
        }
        if (Schema::hasTable('hoc_vien_khong_hoan_thanhs')) {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('hoc_vien_khong_hoan_thanhs as hvkht')
                  ->whereColumn('hvkht.ket_qua_khoa_hoc_id', 'ket_qua_khoa_hocs.id');
            });
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('index')->label('STT')->rowIndex(),

                TextColumn::make('dangKy.hocVien.msnv')
                    ->label('MSNV')->searchable()->sortable(),

                TextColumn::make('dangKy.hocVien.ho_ten')
                    ->label('Họ tên')->searchable()->sortable(),

                TextColumn::make('dangKy.khoaHoc.ma_khoa_hoc')
                    ->label('Mã khóa')->sortable(),

                TextColumn::make('dangKy.khoaHoc.chuongTrinh.ten_chuong_trinh')
                    ->label('Tên khóa học')->toggleable()->limit(60),

                TextColumn::make('diem_chi_tiet')
                    ->label('Điểm chi tiết các buổi')
                    ->html()
                    ->getStateUsing(function (KetQuaKhoaHoc $record) {
                        $rows = DiemDanh::where('dang_ky_id', $record->dang_ky_id)
                            ->orderBy('id')
                            ->get();

                        if ($rows->isEmpty()) return '—';

                        $i = 1;
                        return $rows->map(function ($d) use (&$i) {
                            $diem = is_null($d->diem_buoi_hoc) ? '—' : $d->diem_buoi_hoc;
                            $trangThai = $d->trang_thai ?? '';
                            return 'B' . $i++ . ": {$diem} <em>({$trangThai})</em>";
                        })->implode('<br>');
                    }),

                TextColumn::make('diem_tong_khoa')
                    ->label('Điểm TB khóa')->numeric(2)->sortable(),

                BadgeColumn::make('ket_qua')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'hoan_thanh',
                        'danger'  => 'khong_hoan_thanh',
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'hoan_thanh'       => 'Hoàn thành',
                        'khong_hoan_thanh' => 'Không hoàn thành',
                        default            => '—',
                    })
                    ->sortable(),

                TextColumn::make('ly_do_vang')
                    ->label('Lý do vắng')
                    ->wrap(),

                TextColumn::make('danh_gia_ky_luat_hien_thi')
                    ->label('Đánh giá kỷ luật & Đề xuất')
                    ->wrap()
                    ->getStateUsing(function (KetQuaKhoaHoc $record) {
                        $notes = DiemDanh::where('dang_ky_id', $record->dang_ky_id)
                            ->whereNotNull('danh_gia_ky_luat')
                            ->pluck('danh_gia_ky_luat')
                            ->filter()
                            ->unique()
                            ->toArray();
                        return empty($notes) ? '—' : implode('; ', $notes);
                    }),
            ])
            ->filters([
                SelectFilter::make('nam')
                    ->label('Năm')
                    ->options(fn () => LichHoc::query()->select('nam')->whereNotNull('nam')->distinct()->orderByDesc('nam')->pluck('nam', 'nam')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('dangKy.khoaHoc.lichHocs', function ($q) use ($data) {
                                $q->where('nam', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('tuan')
                    ->label('Tuần')
                    ->options(fn () => LichHoc::query()->select('tuan')->whereNotNull('tuan')->distinct()->orderByDesc('tuan')->pluck('tuan', 'tuan')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('dangKy.khoaHoc.lichHocs', function ($q) use ($data) {
                                $q->where('tuan', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->options(fn () => KhoaHoc::query()->orderBy('id', 'desc')->pluck('ma_khoa_hoc', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('dangKy', function ($q) use ($data) {
                                $q->where('khoa_hoc_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->headerActions([
                Action::make('recalcByCourse')
                    ->label('Tính lại theo điểm danh')
                    ->icon('heroicon-o-calculator')
                    ->form([
                        FormsSelect::make('khoa_hoc_id')
                            ->label('Chọn khóa học')
                            ->options(KhoaHoc::orderBy('id', 'desc')->pluck('ma_khoa_hoc', 'id')->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $this->chiTinhDiemTongKhoa((int) $data['khoa_hoc_id']);
                        Notification::make()
                            ->title('Đã tính lại Điểm TB theo điểm danh (đã đưa vào danh sách chờ duyệt)')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label('Cập nhật')
                    ->modalHeading('Cập nhật kết quả học viên')
                    ->form([
                        FormsTextInput::make('diem_tong_khoa')
                            ->label('Điểm TB khóa')->numeric()->minValue(0)->maxValue(10)->step(0.01)->nullable(),

                        FormsSelect::make('ket_qua')
                            ->label('Trạng thái')
                            ->options([
                                'hoan_thanh'       => 'Hoàn thành',
                                'khong_hoan_thanh' => 'Không hoàn thành',
                            ])
                            ->required()
                            ->native(false),

                        FormsTextarea::make('ly_do_vang')
                            ->label('Lý do vắng (tổng hợp)')
                            ->rows(3),

                        FormsTextarea::make('danh_gia_ky_luat')
                            ->label('Đánh giá kỷ luật & Đề xuất')
                            ->rows(3),
                    ])
                    ->using(function (KetQuaKhoaHoc $record, array $data): KetQuaKhoaHoc {
                        // Ghi nhận quyết định & tắt cờ chờ duyệt
                        $record->fill([
                            'diem_tong_khoa' => $data['diem_tong_khoa'] ?? null,
                            'ket_qua'        => $data['ket_qua'],
                            'ly_do_vang'     => $data['ly_do_vang'] ?? null,
                            'needs_review'   => false,
                        ])->save();

                        if (!empty($data['danh_gia_ky_luat'])) {
                            DiemDanh::where('dang_ky_id', $record->dang_ky_id)
                                ->update(['danh_gia_ky_luat' => $data['danh_gia_ky_luat']]);
                        }
                        return $record;
                    }),
            ])
            ->bulkActions([
                BulkAction::make('bulkUpdate')
                    ->label('Cập nhật hàng loạt')
                    ->icon('heroicon-o-check-circle')
                    ->deselectRecordsAfterCompletion()
                    ->form([
                        FormsSelect::make('ket_qua')
                            ->label('Trạng thái')
                            ->options([
                                'hoan_thanh'       => 'Hoàn thành',
                                'khong_hoan_thanh' => 'Không hoàn thành',
                            ])
                            ->required()
                            ->native(false),
                        FormsTextarea::make('danh_gia_ky_luat')
                            ->label('Đánh giá kỷ luật & Đề xuất (áp cho các bản ghi chọn)')
                            ->rows(2),
                    ])
                    ->action(function (array $data, \Illuminate\Support\Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            /** @var KetQuaKhoaHoc $record */
                            $record->fill([
                                'ket_qua'      => $data['ket_qua'],
                                'needs_review' => false,
                            ])->save();

                            if (!empty($data['danh_gia_ky_luat'])) {
                                DiemDanh::where('dang_ky_id', $record->dang_ky_id)
                                    ->update(['danh_gia_ky_luat' => $data['danh_gia_ky_luat']]);
                            }
                            $count++;
                        }
                        Notification::make()->title("Đã cập nhật {$count} học viên.")->success()->send();
                    }),
            ])
            ->emptyStateHeading('Chưa có dữ liệu')
            ->emptyStateDescription('Chọn Năm/Tuần/Khóa học hoặc dùng nút "Tính lại theo điểm danh".');
    }

    /**
     * Tính Điểm TB theo điểm danh và đưa vào danh sách CHỜ DUYỆT.
     */
    private function chiTinhDiemTongKhoa(int $khoaHocId): void
    {
        $dangKies = DangKy::where('khoa_hoc_id', $khoaHocId)->get();

        foreach ($dangKies as $dk) {
            $diemDanhs = DiemDanh::where('dang_ky_id', $dk->id)->get();

            $tongDiem = 0; $soBuoiCoDiem = 0;
            foreach ($diemDanhs as $dd) {
                if (!is_null($dd->diem_buoi_hoc)) {
                    $tongDiem += (float) $dd->diem_buoi_hoc;
                    $soBuoiCoDiem++;
                }
            }
            $diemTongKhoa = $soBuoiCoDiem > 0 ? round($tongDiem / max(1, $soBuoiCoDiem), 2) : null;

            // Tính lại & đưa vào chờ duyệt
            KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dk->id],
                ['diem_tong_khoa' => $diemTongKhoa, 'needs_review' => true, 'ket_qua' => null]
            );
        }
    }
}
