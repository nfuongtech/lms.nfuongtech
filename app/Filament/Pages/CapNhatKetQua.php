<?php

namespace App\Filament\Pages;

use App\Models\KetQuaKhoaHoc;
use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\KhoaHoc;

use Filament\Pages\Page;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\TextInput as FormsTextInput;
use Filament\Forms\Components\Toggle as FormsToggle;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkAction;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class CapNhatKetQua extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Cập nhật kết quả học tập';
    protected static ?string $title           = 'Cập nhật kết quả học tập';

    // View blade hợp lệ
    protected static string $view             = 'filament.pages.cap-nhat-ket-qua';

    public static function getSlug(): string
    {
        return 'cap-nhat-ket-qua';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KetQuaKhoaHoc::query()
                    ->with([
                        // CHỈ load các cột có thật
                        'dangKy.hocVien:id,msnv,ho_ten',
                        'dangKy.khoaHoc:id,ma_khoa_hoc',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('dangKy.hocVien.msnv')
                    ->label('MSNV')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dangKy.hocVien.ho_ten')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dangKy.khoaHoc.ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('diem_tong_khoa')
                    ->label('Điểm TB khóa')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('ket_qua')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'hoan_thanh',
                        'danger'  => 'khong_hoan_thanh',
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'hoan_thanh'       => 'Đạt / Hoàn thành',
                        'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                        default            => '—',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('can_hoc_lai')
                    ->label('Đề xuất học lại')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                // Lọc theo Khóa học
                Tables\Filters\Filter::make('khoa_hoc')
                    ->label('Khóa học')
                    ->form([
                        FormsSelect::make('khoa_hoc_id')
                            ->label('Chọn khóa học')
                            ->options(fn () => KhoaHoc::orderBy('ma_khoa_hoc', 'asc')
                                ->pluck('ma_khoa_hoc', 'id')->toArray())
                            ->searchable()
                            ->placeholder('— Tất cả —'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $kh = $data['khoa_hoc_id'] ?? null;
                        if ($kh) {
                            $query->whereHas('dangKy', fn (Builder $q) => $q->where('khoa_hoc_id', $kh));
                        }
                        return $query;
                    }),

                // Lọc theo Trạng thái
                Tables\Filters\SelectFilter::make('ket_qua')
                    ->label('Trạng thái')
                    ->options([
                        'hoan_thanh'       => 'Đạt / Hoàn thành',
                        'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                    ])
                    ->native(false),

                // Tìm theo MSNV/Họ tên
                Tables\Filters\Filter::make('tu_khoa')
                    ->label('Tìm kiếm')
                    ->form([
                        FormsTextInput::make('q')->placeholder('Nhập MSNV hoặc Họ tên'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $q = trim((string)($data['q'] ?? ''));
                        if ($q !== '') {
                            $query->whereHas('dangKy.hocVien', function (Builder $sub) use ($q) {
                                $sub->where('msnv', 'like', "%{$q}%")
                                    ->orWhere('ho_ten', 'like', "%{$q}%");
                            });
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                // TÍNH LẠI THEO ĐIỂM DANH — chọn Khóa học ngay trong action
                Action::make('recalc')
                    ->label('Tính lại theo điểm danh')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        FormsSelect::make('khoa_hoc_id')
                            ->label('Chọn khóa học để tính lại')
                            ->options(fn () => KhoaHoc::orderBy('ma_khoa_hoc', 'asc')
                                ->pluck('ma_khoa_hoc', 'id')->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $khoaHocId = (int) $data['khoa_hoc_id'];
                        $this->tinhToanKetQuaTheoKhoaHoc($khoaHocId);

                        Notification::make()
                            ->title('Đã tính lại theo điểm danh')
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
                            ->label('Điểm TB khóa')
                            ->numeric()
                            ->minValue(0)->maxValue(10)
                            ->step(0.01)
                            ->nullable(),

                        FormsSelect::make('ket_qua')
                            ->label('Trạng thái')
                            ->options([
                                'hoan_thanh'       => 'Đạt / Hoàn thành',
                                'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                            ])
                            ->required()
                            ->native(false),

                        FormsToggle::make('can_hoc_lai')
                            ->label('Đề xuất học lại')
                            ->inline(false),
                    ])
                    ->using(function (KetQuaKhoaHoc $record, array $data): KetQuaKhoaHoc {
                        $record->fill([
                            'diem_tong_khoa' => $data['diem_tong_khoa'] ?? null,
                            'ket_qua'        => $data['ket_qua'],
                            'can_hoc_lai'    => (bool)($data['can_hoc_lai'] ?? false),
                        ])->save();

                        // Observer sẽ tự đồng bộ sang 2 bảng HV hoàn thành/không hoàn thành
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
                                'hoan_thanh'       => 'Đạt / Hoàn thành',
                                'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                            ])
                            ->required()
                            ->native(false),
                        FormsToggle::make('can_hoc_lai')
                            ->label('Đề xuất học lại')
                            ->inline(false),
                    ])
                    ->action(function (array $data, \Illuminate\Support\Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            /** @var KetQuaKhoaHoc $record */
                            $record->fill([
                                'ket_qua'     => $data['ket_qua'],
                                'can_hoc_lai' => (bool)($data['can_hoc_lai'] ?? false),
                            ])->save();
                            $count++;
                        }
                        Notification::make()
                            ->title("Đã cập nhật {$count} học viên.")
                            ->success()->send();
                    }),
            ])
            ->emptyStateHeading('Chưa có dữ liệu')
            ->emptyStateDescription('Vui lòng chọn bộ lọc hoặc dùng nút "Tính lại theo điểm danh".');
    }

    /**
     * Tính lại kết quả cho toàn bộ học viên của một Khóa học
     * - Quy tắc: vắng ≤ 20% và (điểm TB null hoặc ≥ 5) → hoan_thanh; ngược lại khong_hoan_thanh.
     * - Lưu vào ket_qua_khoa_hocs; Observer sẽ tự đồng bộ sang 2 bảng HV hoàn thành/không hoàn thành.
     */
    private function tinhToanKetQuaTheoKhoaHoc(int $khoaHocId): void
    {
        $dangKies = DangKy::where('khoa_hoc_id', $khoaHocId)->get();

        foreach ($dangKies as $dk) {
            $diemDanhs = DiemDanh::where('dang_ky_id', $dk->id)->get();

            $tongDiem = 0;
            $soBuoiCoDiem = 0;
            $soBuoiVang = 0;
            $tongSoBuoi = $diemDanhs->count();

            foreach ($diemDanhs as $dd) {
                if (!is_null($dd->diem_buoi_hoc)) {
                    $tongDiem += (float) $dd->diem_buoi_hoc;
                    $soBuoiCoDiem++;
                }
                if (in_array($dd->trang_thai, ['vang_phep', 'vang_khong_phep'], true)) {
                    $soBuoiVang++;
                }
            }

            $diemTongKhoa = $soBuoiCoDiem > 0 ? round($tongDiem / max(1, $soBuoiCoDiem), 2) : null;
            $tyLeVang     = $tongSoBuoi > 0 ? ($soBuoiVang / $tongSoBuoi) * 100 : 0;

            $ketQua = ($tyLeVang <= 20 && ($diemTongKhoa === null || $diemTongKhoa >= 5))
                ? 'hoan_thanh'
                : 'khong_hoan_thanh';

            KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dk->id],
                [
                    'diem_tong_khoa' => $diemTongKhoa,
                    'ket_qua'        => $ketQua,
                    // FIX ở đây: dùng $ketQua (camelCase), không phải $ket_qua
                    'can_hoc_lai'    => $ketQua === 'khong_hoan_thanh' ? 1 : 0,
                ]
            );
        }
    }
}
