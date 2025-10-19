<?php

namespace App\Filament\Resources;

use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class HocVienKhongHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienKhongHoanThanh::class;
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';

    public static function getSlug(): string
    {
        return 'hoc-vien-khong-hoan-thanhs';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('STT')->rowIndex(),
                Tables\Columns\TextColumn::make('hocVien.msnv')->label('MS')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Họ & Tên')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã khóa')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('ketQua.diem_trung_binh')->label('ĐTB')->numeric(2)->sortable(),
                Tables\Columns\TextColumn::make('ketQua.tong_so_gio_thuc_te')->label('Giờ thực học')->numeric(2),
                Tables\Columns\TextColumn::make('ly_do_khong_hoan_thanh')->label('Lý do không hoàn thành')->wrap()->toggleable(),
                IconColumn::make('co_the_ghi_danh_lai')->label('Có thể ghi danh lại')->boolean(),
            ])
            ->actions([
                Action::make('cap_nhat')
                    ->label('Cập nhật')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('ket_qua')
                            ->label('Kết quả chính thức')
                            ->options([
                                'khong_hoan_thanh' => 'Giữ Không hoàn thành',
                                'hoan_thanh' => 'Chuyển sang Hoàn thành',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('ly_do_khong_hoan_thanh')->label('Lý do / Ghi chú')->rows(3),
                        Forms\Components\Toggle::make('co_the_ghi_danh_lai')->label('Đề xuất ghi danh lại'),
                    ])
                    ->fillForm(fn (HocVienKhongHoanThanh $record) => [
                        'ket_qua' => 'khong_hoan_thanh',
                        'ly_do_khong_hoan_thanh' => $record->ly_do_khong_hoan_thanh,
                        'co_the_ghi_danh_lai' => $record->co_the_ghi_danh_lai,
                    ])
                    ->action(function (HocVienKhongHoanThanh $record, array $data): void {
                        $ketQua = HocVienHoanThanhResource::normalizeKetQua($data['ket_qua'] ?? 'khong_hoan_thanh');
                        $ketQuaModel = $record->ketQua;
                        if ($ketQuaModel) {
                            $ketQuaModel->ket_qua = $ketQua;
                            $ketQuaModel->ket_qua_goi_y = $ketQuaModel->ket_qua_goi_y ?? $ketQua;
                            $ketQuaModel->can_hoc_lai = $ketQua === 'khong_hoan_thanh';
                            $ketQuaModel->save();
                        }

                        if ($ketQua === 'hoan_thanh') {
                            HocVienHoanThanh::updateOrCreate(
                                [
                                    'hoc_vien_id' => $record->hoc_vien_id,
                                    'khoa_hoc_id' => $record->khoa_hoc_id,
                                    'ket_qua_khoa_hoc_id' => $record->ket_qua_khoa_hoc_id,
                                ],
                                []
                            );

                            $record->delete();
                        } else {
                            $record->update([
                                'ly_do_khong_hoan_thanh' => $data['ly_do_khong_hoan_thanh'] ?? null,
                                'co_the_ghi_danh_lai' => $data['co_the_ghi_danh_lai'] ?? false,
                            ]);
                        }

                        Notification::make()->title('Đã cập nhật tình trạng học viên')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => HocVienKhongHoanThanhResource\Pages\ListHocVienKhongHoanThanhs::route('/'),
        ];
    }
}
