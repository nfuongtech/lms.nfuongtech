<?php

namespace App\Filament\Resources;

use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HocVienHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienHoanThanh::class;
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';

    public static function getSlug(): string
    {
        return 'hoc-vien-hoan-thanhs';
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
                BadgeColumn::make('ketQua.ket_qua')
                    ->label('Kết quả')
                    ->colors([
                        'success' => fn ($state) => $state === 'hoan_thanh',
                        'danger' => fn ($state) => $state === 'khong_hoan_thanh',
                    ])
                    ->formatStateUsing(fn (?string $state) => $state === 'khong_hoan_thanh' ? 'Không hoàn thành' : 'Hoàn thành'),
                Tables\Columns\TextColumn::make('chi_phi_dao_tao')->label('Chi phí đào tạo')->money('VND', true)->toggleable(),
                Tables\Columns\TextColumn::make('chung_chi_link')
                    ->label('Link chứng chỉ')
                    ->url(function (HocVienHoanThanh $record): ?string {
                        $link = trim((string) ($record->chung_chi_link ?? ''));
                        return $link !== '' ? $link : null;
                    }, shouldOpenInNewTab: true)
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('chung_chi_tap_tin')
                    ->label('File chứng chỉ')
                    ->url(function (HocVienHoanThanh $record): ?string {
                        if (!$record->chung_chi_tap_tin) {
                            return null;
                        }

                        if (Str::startsWith($record->chung_chi_tap_tin, ['http://', 'https://'])) {
                            return $record->chung_chi_tap_tin;
                        }

                        try {
                            return Storage::disk('public')->url($record->chung_chi_tap_tin);
                        } catch (\Throwable $exception) {
                            return null;
                        }
                    }, shouldOpenInNewTab: true)
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ngay_hoan_thanh')->label('Ngày hoàn thành')->date()->sortable(),
                Tables\Columns\TextColumn::make('ghi_chu')->label('Ghi chú')->wrap()->toggleable(),
            ])
            ->actions([
                Action::make('cap_nhat')
                    ->label('Cập nhật')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\Select::make('ket_qua')
                            ->label('Kết quả cuối cùng')
                            ->options([
                                'hoan_thanh' => 'Hoàn thành',
                                'khong_hoan_thanh' => 'Không hoàn thành',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('ngay_hoan_thanh')->label('Ngày hoàn thành')->closeOnDateSelection(),
                        Forms\Components\TextInput::make('chi_phi_dao_tao')->label('Chi phí đào tạo')->numeric()->prefix('VND')->nullable(),
                        Forms\Components\Toggle::make('chung_chi_da_cap')->label('Đã cấp chứng chỉ'),
                        Forms\Components\TextInput::make('chung_chi_link')->label('Link chứng chỉ')->url()->maxLength(255),
                        Forms\Components\FileUpload::make('chung_chi_tap_tin')
                            ->label('Tập tin chứng chỉ (PDF)')
                            ->directory('chung-chi')
                            ->disk('public')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->nullable(),
                        Forms\Components\Textarea::make('ghi_chu')->label('Ghi chú')->rows(3),
                    ])
                    ->fillForm(function (HocVienHoanThanh $record): array {
                        return [
                            'ket_qua' => $record->ketQua?->ket_qua ?? 'hoan_thanh',
                            'ngay_hoan_thanh' => $record->ngay_hoan_thanh,
                            'chi_phi_dao_tao' => $record->chi_phi_dao_tao,
                            'chung_chi_da_cap' => $record->chung_chi_da_cap,
                            'chung_chi_link' => $record->chung_chi_link,
                            'chung_chi_tap_tin' => $record->chung_chi_tap_tin,
                            'ghi_chu' => $record->ghi_chu,
                        ];
                    })
                    ->action(function (HocVienHoanThanh $record, array $data): void {
                        $ketQua = self::normalizeKetQua($data['ket_qua'] ?? 'hoan_thanh');
                        $ketQuaModel = $record->ketQua;

                        if ($ketQuaModel) {
                            $ketQuaModel->ket_qua = $ketQua;
                            $ketQuaModel->ket_qua_goi_y = $ketQuaModel->ket_qua_goi_y ?? $ketQua;
                            $ketQuaModel->can_hoc_lai = $ketQua === 'khong_hoan_thanh';
                            $ketQuaModel->save();
                        }

                        if ($ketQua === 'hoan_thanh') {
                            $record->update([
                                'ngay_hoan_thanh' => $data['ngay_hoan_thanh'] ?? null,
                                'chi_phi_dao_tao' => $data['chi_phi_dao_tao'] ?? null,
                                'chung_chi_da_cap' => $data['chung_chi_da_cap'] ?? false,
                                'chung_chi_link' => $data['chung_chi_link'] ?? null,
                                'chung_chi_tap_tin' => $data['chung_chi_tap_tin'] ?? null,
                                'ghi_chu' => $data['ghi_chu'] ?? null,
                            ]);

                            HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $record->ket_qua_khoa_hoc_id)->delete();
                        } else {
                            HocVienKhongHoanThanh::updateOrCreate(
                                [
                                    'hoc_vien_id' => $record->hoc_vien_id,
                                    'khoa_hoc_id' => $record->khoa_hoc_id,
                                    'ket_qua_khoa_hoc_id' => $record->ket_qua_khoa_hoc_id,
                                ],
                                [
                                    'ly_do_khong_hoan_thanh' => $data['ghi_chu'] ?? null,
                                ]
                            );

                            $record->delete();
                        }

                        Notification::make()->title('Đã cập nhật kết quả học viên')->success()->send();
                    })
                    ->visible(fn (HocVienHoanThanh $record) => (bool) $record->ketQua),
            ])
            ->bulkActions([]);
    }

    public static function normalizeKetQua(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        return match ($normalized) {
            'khong_hoan_thanh', 'không hoàn thành', 'khong hoan thanh' => 'khong_hoan_thanh',
            default => 'hoan_thanh',
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => HocVienHoanThanhResource\Pages\ListHocVienHoanThanhs::route('/'),
        ];
    }
}
