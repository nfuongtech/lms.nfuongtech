<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class LichHocsRelationManager extends RelationManager
{
    protected static string $relationship = 'lichHocs';
    protected static ?string $recordTitleAttribute = 'ngay_hoc';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('chuyen_de_id')
                ->label('Chuyên đề')
                ->relationship('chuyenDe', 'ten_chuyen_de')
                ->required()
                ->reactive(),

            Forms\Components\Select::make('giang_vien_id')
                ->label('Giảng viên')
                ->options(fn ($get) => 
                    \App\Models\ChuyenDe::find($get('chuyen_de_id'))
                        ?->giangViens()
                        ->pluck('ho_ten', 'id') ?? []
                )
                ->required(),

            Forms\Components\DatePicker::make('ngay_hoc')
                ->label('Ngày học')
                ->required(),

            Forms\Components\TimePicker::make('gio_bat_dau')
                ->label('Giờ bắt đầu')
                ->seconds(false)
                ->required(),

            Forms\Components\TimePicker::make('gio_ket_thuc')
                ->label('Giờ kết thúc')
                ->seconds(false)
                ->required(),

            Forms\Components\TextInput::make('phong_hoc')
                ->label('Phòng học')
                ->required(),
        ])
        ->rules([
            function ($get) {
                return function (string $attribute, $value, \Closure $fail) use ($get) {
                    $exists = \App\Models\LichHoc::where('ngay_hoc', $get('ngay_hoc'))
                        ->where(function ($q) use ($get) {
                            $q->whereBetween('gio_bat_dau', [$get('gio_bat_dau'), $get('gio_ket_thuc')])
                              ->orWhereBetween('gio_ket_thuc', [$get('gio_bat_dau'), $get('gio_ket_thuc')]);
                        })
                        ->where(function ($q) use ($get) {
                            $q->where('giang_vien_id', $get('giang_vien_id'))
                              ->orWhere('phong_hoc', $get('phong_hoc'));
                        })
                        ->exists();

                    if ($exists) {
                        $fail("Giảng viên hoặc phòng học đã bị trùng lịch.");
                    }
                };
            }
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề'),
            Tables\Columns\TextColumn::make('giangVien.ho_ten')->label('Giảng viên'),
            Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày học')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('gio_bat_dau')->label('Giờ bắt đầu'),
            Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Giờ kết thúc'),
            Tables\Columns\TextColumn::make('phong_hoc')->label('Phòng học'),
        ])
        ->headerActions([Tables\Actions\CreateAction::make()])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
