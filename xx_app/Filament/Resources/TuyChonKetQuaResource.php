<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TuyChonKetQuaResource\Pages;
// use App\Filament\Resources\TuyChonKetQuaResource\RelationManagers; // Bỏ comment nếu có
use App\Models\TuyChonKetQua;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// --- Thêm cho liên kết modules ---
use App\Models\HocVien; // Giả định có model HocVien
use App\Models\KetQuaKhoaHoc; // Giả định có model KetQuaKhoaHoc
use App\Models\DangKy; // Giả định có model DangKy
// --- Hết thêm cho liên kết modules ---

class TuyChonKetQuaResource extends Resource
{
    protected static ?string $model = TuyChonKetQua::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $modelLabel = 'Tùy chọn bổ sung';
    protected static ?string $pluralModelLabel = 'Tùy chọn bổ sung';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('loai')
                        ->label('Loại tùy chọn')
                        ->options([
                            'chuyen_can' => 'Chuyên cần',
                            'ket_qua' => 'Kết quả',
                            'tinh_trang_hoc_vien' => 'Tình trạng học viên',
                            'trang_thai_khoa_hoc' => 'Trạng thái khóa học',
                            'ly_do_vang' => 'Lý do vắng',
                            'ly_do_khong_hoan_thanh' => 'Lý do không hoàn thành',
                            // Thêm các loại khác nếu cần
                        ])
                        ->required()
                        ->searchable()
                        ->preload()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('gia_tri')
                        ->label('Giá trị tùy chọn')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loai')
                    ->label('Loại tùy chọn')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'chuyen_can' => 'primary',
                        'ket_qua' => 'success',
                        'tinh_trang_hoc_vien' => 'warning',
                        'trang_thai_khoa_hoc' => 'info',
                        'ly_do_vang' => 'danger',
                        'ly_do_khong_hoan_thanh' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('gia_tri')
                    ->label('Giá trị')
                    ->searchable()
                    ->sortable(),
                // --- THÊM: Cột hiển thị số lượng bản ghi liên kết ---
                Tables\Columns\TextColumn::make('lien_ket_count')
                    ->label('Số bản ghi liên kết')
                    ->getStateUsing(function (TuyChonKetQua $record): int {
                        // Đếm số bản ghi trong các bảng liên kết sử dụng giá trị này
                        $count = 0;

                        // Ví dụ: Đếm trong bảng hoc_viens (cột tinh_trang)
                        if ($record->loai === 'tinh_trang_hoc_vien') {
                            $count += HocVien::where('tinh_trang', $record->gia_tri)->count();
                        }

                        // Ví dụ: Đếm trong bảng ket_qua_khoa_hocs (cột ket_qua)
                        if ($record->loai === 'ket_qua') {
                            $count += KetQuaKhoaHoc::where('ket_qua', $record->gia_tri)->count();
                        }

                        // Ví dụ: Đếm trong bảng dang_kies (cột ly_do_vang)
                        if ($record->loai === 'ly_do_vang') {
                            $count += DangKy::where('ly_do_vang', $record->gia_tri)->count();
                        }

                        // Thêm các bảng khác nếu cần
                        // ...

                        return $count;
                    })
                    ->alignCenter()
                    ->sortable(),
                // --- HẾT THÊM: Cột hiển thị số lượng bản ghi liên kết ---
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('loai')
                    ->label('Loại tùy chọn')
                    ->options([
                        'chuyen_can' => 'Chuyên cần',
                        'ket_qua' => 'Kết quả',
                        'tinh_trang_hoc_vien' => 'Tình trạng học viên',
                        'trang_thai_khoa_hoc' => 'Trạng thái khóa học',
                        'ly_do_vang' => 'Lý do vắng',
                        'ly_do_khong_hoan_thanh' => 'Lý do không hoàn thành',
                        // Thêm các loại khác nếu cần
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Xóa Tùy chọn Kết quả')
                    ->modalDescription('Bạn có chắc chắn muốn xóa tùy chọn này? Hành động này không thể hoàn tác.')
                    ->action(function (TuyChonKetQua $record) {
                        // Kiểm tra xem có bản ghi nào đang dùng tùy chọn này không
                        $count = 0;

                        // Ví dụ: Kiểm tra trong bảng hoc_viens (cột tinh_trang)
                        if ($record->loai === 'tinh_trang_hoc_vien') {
                            $count += HocVien::where('tinh_trang', $record->gia_tri)->count();
                        }

                        // Ví dụ: Kiểm tra trong bảng ket_qua_khoa_hocs (cột ket_qua)
                        if ($record->loai === 'ket_qua') {
                            $count += KetQuaKhoaHoc::where('ket_qua', $record->gia_tri)->count();
                        }

                        // Ví dụ: Kiểm tra trong bảng dang_kies (cột ly_do_vang)
                        if ($record->loai === 'ly_do_vang') {
                            $count += DangKy::where('ly_do_vang', $record->gia_tri)->count();
                        }

                        // Thêm các bảng khác nếu cần
                        // ...

                        if ($count > 0) {
                            // Không cho phép xóa nếu có bản ghi đang dùng
                            \Filament\Notifications\Notification::make()
                                ->title('Không thể xóa')
                                ->body("Tùy chọn này đang được sử dụng bởi $count bản ghi. Vui lòng cập nhật các bản ghi trước khi xóa.")
                                ->danger()
                                ->send();
                            return;
                        }

                        // Nếu không có bản ghi nào dùng, cho phép xóa
                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Xóa thành công')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Xóa các Tùy chọn Kết quả')
                        ->modalDescription('Bạn có chắc chắn muốn xóa các tùy chọn đã chọn? Hành động này không thể hoàn tác.')
                        ->action(function ($records) {
                            $notDeleted = [];
                            $deletedCount = 0;

                            foreach ($records as $record) {
                                // Kiểm tra xem có bản ghi nào đang dùng tùy chọn này không
                                $count = 0;

                                // Ví dụ: Kiểm tra trong bảng hoc_viens (cột tinh_trang)
                                if ($record->loai === 'tinh_trang_hoc_vien') {
                                    $count += HocVien::where('tinh_trang', $record->gia_tri)->count();
                                }

                                // Ví dụ: Kiểm tra trong bảng ket_qua_khoa_hocs (cột ket_qua)
                                if ($record->loai === 'ket_qua') {
                                    $count += KetQuaKhoaHoc::where('ket_qua', $record->gia_tri)->count();
                                }

                                // Ví dụ: Kiểm tra trong bảng dang_kies (cột ly_do_vang)
                                if ($record->loai === 'ly_do_vang') {
                                    $count += DangKy::where('ly_do_vang', $record->gia_tri)->count();
                                }

                                // Thêm các bảng khác nếu cần
                                // ...

                                if ($count > 0) {
                                    // Không cho phép xóa nếu có bản ghi đang dùng
                                    $notDeleted[] = $record->gia_tri;
                                } else {
                                    // Nếu không có bản ghi nào dùng, cho phép xóa
                                    $record->delete();
                                    $deletedCount++;
                                }
                            }

                            if (!empty($notDeleted)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Không thể xóa một số tùy chọn')
                                    ->body('Các tùy chọn sau đang được sử dụng: ' . implode(', ', $notDeleted))
                                    ->warning()
                                    ->send();
                            }

                            if ($deletedCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Đã xóa $deletedCount tùy chọn")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->paginated([10, 25, 50, 'all']);
    }

    public static function getRelations(): array
    {
        return [
            // Thêm RelationManagers nếu có
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTuyChonKetQuas::route('/'),
            'create' => Pages\CreateTuyChonKetQua::route('/create'),
            'edit' => Pages\EditTuyChonKetQua::route('/{record}/edit'),
        ];
    }
}
