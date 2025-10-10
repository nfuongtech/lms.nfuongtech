<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\DiaDiemDaoTao;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\DB;

class LichHocsRelationManager extends RelationManager
{
    protected static string $relationship = 'lichHocs';
    protected static ?string $title = 'Lịch đào tạo';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('chuyen_de_id')
                ->label('Chuyên đề/Học phần')
                ->options(function () {
                    $owner = $this->getOwnerRecord();
                    if (! $owner?->chuong_trinh_id) return [];
                    $ids = DB::table('chuong_trinh_chuyen_de')
                        ->where('chuong_trinh_id', $owner->chuong_trinh_id)
                        ->pluck('chuyen_de_id');
                    return ChuyenDe::whereIn('id', $ids)
                        ->orderBy('ten_chuyen_de')->pluck('ten_chuyen_de','id');
                })
                ->searchable()->preload()->required()->reactive(),

            Forms\Components\Select::make('giang_vien_id')
                ->label('Giảng viên')
                ->options(function (Forms\Get $get) {
                    $cd = $get('chuyen_de_id');
                    if (! $cd) return [];
                    $gvIds = DB::table('chuyen_de_giang_vien')->where('chuyen_de_id', $cd)->pluck('giang_vien_id');
                    return GiangVien::whereIn('id', $gvIds)->orderBy('ho_ten')->pluck('ho_ten','id');
                })
                ->searchable()->preload()->required(),

            Forms\Components\Select::make('dia_diem_id')
                ->label('Địa điểm (phòng)')
                ->options(fn () => DiaDiemDaoTao::orderBy('ten_phong')->pluck('ten_phong','id'))
                ->searchable()->preload()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    $name = $state ? optional(DiaDiemDaoTao::find($state))->ten_phong : null;
                    if ($name) $set('dia_diem', $name); // ẩn, ghi xuống DB
                }),

            Forms\Components\Hidden::make('dia_diem'),

            Forms\Components\DatePicker::make('ngay_hoc')->label('Ngày học')->required()
                ->native(false)->displayFormat('d/m/Y'),

            Forms\Components\TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->seconds(false)->required(),
            Forms\Components\TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->seconds(false)->required(),

            Forms\Components\TextInput::make('so_bai_kiem_tra')->numeric()->default(0)->minValue(0)->label('Số bài kiểm tra'),

            Forms\Components\TextInput::make('so_gio_giang')
                ->label('Số giờ giảng')
                ->numeric()->minValue(1)->default(null)
                ->helperText('Tự tính theo giờ bắt đầu/kết thúc khi lưu (1 giờ = 60 phút). Bạn có thể nhập thủ công để ghi đè.'),
        ])->columns(2);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('ngay_hoc')
            ->columns([
                Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('gio_bat_dau')->label('Bắt đầu')->formatStateUsing(fn ($state) => $state ? substr((string)$state,0,5) : ''),
                Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Kết thúc')->formatStateUsing(fn ($state) => $state ? substr((string)$state,0,5) : ''),
                Tables\Columns\TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề/Học phần')->wrap(),
                Tables\Columns\TextColumn::make('giangVien.ho_ten')->label('Giảng viên')->wrap(),
                // ⬇️ Thêm cột Số giờ giảng (sau Giảng viên)
                Tables\Columns\TextColumn::make('so_gio_giang')->label('Số giờ giảng'),
                Tables\Columns\TextColumn::make('dia_diem')->label('Địa điểm')->wrap(),
                Tables\Columns\TextColumn::make('tuan')->label('Tuần'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Thêm lịch'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
            ]);
    }
}
