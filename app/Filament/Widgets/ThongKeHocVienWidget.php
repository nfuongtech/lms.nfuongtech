<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HocVienResource;
use App\Models\HocVien;
use App\Models\DonVi;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class ThongKeHocVienWidget extends BaseWidget
{
    protected static ?string $heading = 'Thống kê số lượng học viên theo đơn vị';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Lấy dữ liệu thống kê từ HocVienResource (hoặc tự viết lại nếu cần)
        $thongKeData = HocVienResource::getThongKeTheoDonVi(); // Giả định phương thức này trả về array

        // Chuyển đổi dữ liệu thành Collection để Table có thể hiển thị
        $data = collect($thongKeData);

        return $table
            ->query(fn () => $data) // Truyền dữ liệu tĩnh vào
            ->columns([
                Tables\Columns\TextColumn::make('stt')->label('STT')->alignCenter(),
                Tables\Columns\TextColumn::make('thaco_tdtv')->label('THACO/TĐTV'),
                Tables\Columns\TextColumn::make('cong_ty_ban_nvqt')->label('Công ty/Ban NVQT'),
                Tables\Columns\TextColumn::make('so_luong')->label('Số lượng HV (Đang làm việc)')->alignCenter(),
            ])
            ->paginated(false); // Không phân trang cho widget
    }
}
