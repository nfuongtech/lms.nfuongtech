<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\HocVien;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DangKiesRelationManager extends RelationManager
{
    protected static string $relationship = 'dangKies';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hoc_vien_id')
                    ->label('Học viên')
                    ->options(function () {
                        // SỬA LỖI: Kiểm tra $this->getOwnerRecord() trước khi sử dụng
                        $owner = $this->getOwnerRecord();
                        if (!$owner) {
                            return []; // Trả về mảng rỗng nếu không có owner
                        }
                        
                        // Lấy danh sách học viên không thuộc khoá học này
                        return HocVien::whereDoesntHave('dangKies', function ($query) use ($owner) {
                            $query->where('khoa_hoc_id', $owner->id);
                        })->get()->pluck('ten', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('mien_giam_hoc_phi')
                    ->label('Miễn giảm học phí')
                    ->default(false),
                Forms\Components\Textarea::make('ghi_chu')
                    ->label('Ghi chú'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.ten')->label('Tên học viên'),
                Tables\Columns\TextColumn::make('hocVien.ngay_sinh')->label('Ngày sinh')->date('d/m/Y'),
                Tables\Columns\IconColumn::make('mien_giam_hoc_phi')->label('Miễn giảm')->boolean(),
                Tables\Columns\TextColumn::make('ghi_chu')->label('Ghi chú'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
