<?php

namespace App\Filament\Pages;

use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\KetQuaKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\TuyChonKetQua;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CapNhatKetQua extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static string $view = 'filament.pages.cap-nhat-ket-qua';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Cập nhật Kết quả';

    public ?int $selectedKhoaHoc = null;

    public function getFormSchema(): array
    {
        return [
            Select::make('selectedKhoaHoc')
                ->label('Chọn Khóa học')
                ->options(KhoaHoc::where('trang_thai', 'Đã ban hành')->pluck('ten_khoa_hoc', 'id'))
                ->searchable()
                ->live(),
        ];
    }

    public function table(Table $table): Table
    {
        $chuyenCanOptions = TuyChonKetQua::where('loai', 'chuyen_can')->pluck('gia_tri', 'gia_tri');
        $ketQuaOptions = TuyChonKetQua::where('loai', 'ket_qua')->pluck('gia_tri', 'gia_tri');
        $passingStatuses = ['Hoàn thành', 'Đạt yêu cầu'];

        return $table
            ->query(DangKy::query()->where('khoa_hoc_id', $this->selectedKhoaHoc))
            ->columns([
                TextColumn::make('hocVien.ho_ten')->label('Họ tên Học viên'),
                TextColumn::make('hocVien.msnv')->label('MSNV'),
                TextColumn::make('ketQuaKhoaHoc.diem')->label('Điểm cuối khóa')->placeholder('Chưa có'),
                TextColumn::make('ketQuaKhoaHoc.ket_qua')->label('Kết quả cuối khóa')->placeholder('Chưa có'),
                TextColumn::make('ketQuaKhoaHoc.hoc_phi')->label('Học phí (VNĐ)')->money('VND')->placeholder('Chưa có'),
            ])
            ->actions([
                Action::make('update_buoi_hoc')
                    ->label('Cập nhật Buổi học')
                    ->icon('heroicon-o-calendar')
                    ->form([
                        Select::make('lich_hoc_id')
                            ->label('Chọn Buổi học')
                            ->options(fn(DangKy $record) => $record->khoaHoc->lichHocs->pluck('ngay_hoc', 'id')->mapWithKeys(fn($ngayHoc, $id) => [$id => \Carbon\Carbon::parse($ngayHoc)->format('d/m/Y')]))
                            ->required()
                            ->live(),
                        Select::make('trang_thai')
                            ->label('Trạng thái chuyên cần')
                            ->options($chuyenCanOptions)
                            ->live() // Bật chế độ tương tác
                            ->default(function (DangKy $record, $get) {
                                $lichHocId = $get('lich_hoc_id');
                                if (!$lichHocId) return null;
                                return $record->diemDanhs()->where('lich_hoc_id', $lichHocId)->first()?->trang_thai;
                            }),
                        TextInput::make('diem_buoi_hoc')
                            ->label('Điểm buổi học')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->default(function (DangKy $record, $get) {
                                $lichHocId = $get('lich_hoc_id');
                                if (!$lichHocId) return null;
                                return $record->diemDanhs()->where('lich_hoc_id', $lichHocId)->first()?->diem_buoi_hoc;
                            }),
                        // Thêm ô nhập Lý do vắng
                        Textarea::make('ly_do_vang')
                            ->label('Lý do vắng')
                            ->visible(fn ($get) => in_array($get('trang_thai'), ['Phép', 'Không phép'])) // Chỉ hiển thị khi vắng
                            ->default(function (DangKy $record, $get) {
                                $lichHocId = $get('lich_hoc_id');
                                if (!$lichHocId) return null;
                                return $record->diemDanhs()->where('lich_hoc_id', $lichHocId)->first()?->ly_do_vang;
                            }),
                    ])
                    ->action(function (DangKy $record, array $data) {
                        $trangThai = $data['trang_thai'];
                        $lyDoVang = in_array($trangThai, ['Phép', 'Không phép']) ? $data['ly_do_vang'] : null;

                        DiemDanh::updateOrCreate(
                            ['dang_ky_id' => $record->id, 'lich_hoc_id' => $data['lich_hoc_id']],
                            [
                                'trang_thai' => $trangThai,
                                'diem_buoi_hoc' => $data['diem_buoi_hoc'],
                                'ly_do_vang' => $lyDoVang,
                            ]
                        );
                        Notification::make()->title('Cập nhật buổi học thành công')->success()->send();
                    }),

                Action::make('update_ket_qua')
                    ->label('Cập nhật KQ Cuối khóa')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        TextInput::make('diem')
                            ->label('Điểm cuối khóa')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->default(fn (DangKy $record) => $record->ketQuaKhoaHoc?->diem)
                            ->suffixAction(
                                FormAction::make('tinh_diem_tb')
                                    ->label('Tính ĐTB')
                                    ->icon('heroicon-o-calculator')
                                    ->action(function (DangKy $record, $set) {
                                        $avgScore = $record->diemDanhs()->where('diem_buoi_hoc', '>', 0)->avg('diem_buoi_hoc');
                                        $set('diem', round($avgScore, 2));
                                    })
                            ),
                        Select::make('ket_qua')
                            ->label('Kết quả cuối khóa')
                            ->options($ketQuaOptions)
                            ->default(fn (DangKy $record) => $record->ketQuaKhoaHoc?->ket_qua)
                            ->live(),
                        TextInput::make('hoc_phi')
                            ->label('Học phí (VNĐ)')
                            ->numeric()
                            ->default(fn (DangKy $record) => $record->ketQuaKhoaHoc?->hoc_phi)
                            ->visible(fn ($get) => in_array($get('ket_qua'), $passingStatuses)),
                    ])
                    ->action(function (DangKy $record, array $data) use ($passingStatuses) {
                        $ketQua = KetQuaKhoaHoc::firstOrNew(['dang_ky_id' => $record->id]);
                        $ketQua->diem = $data['diem'];
                        $ketQua->ket_qua = $data['ket_qua'];
                        if (in_array($data['ket_qua'], $passingStatuses)) {
                            $ketQua->hoc_phi = $data['hoc_phi'];
                        } else {
                            $ketQua->hoc_phi = null;
                        }
                        $ketQua->save();
                        Notification::make()->title('Đã cập nhật thành công')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
