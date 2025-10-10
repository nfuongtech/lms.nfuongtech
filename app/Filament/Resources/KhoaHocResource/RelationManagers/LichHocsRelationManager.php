<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\DiaDiemDaoTao;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LichHocsRelationManager extends RelationManager
{
    protected static string $relationship = 'lichHocs';
    protected static ?string $title = 'Tạo lịch học';

    /** Lấy chủ sở hữu (KhoaHoc) an toàn, ưu tiên getOwnerRecord() */
    private function currentOwner(): ?KhoaHoc
    {
        try {
            if (method_exists($this, 'getOwnerRecord')) {
                $o = $this->getOwnerRecord();
                if ($o instanceof KhoaHoc) return $o;
            }
        } catch (\Throwable $e) {
            // fallback route id
        }
        $id = request()->route('record') ?? request()->route('id') ?? null;
        return $id ? KhoaHoc::find($id) : null;
    }

    private static function detectTable(array $candidates): ?string
    {
        foreach ($candidates as $t) {
            if (Schema::hasTable($t)) return $t;
        }
        return null;
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\DatePicker::make('ngay_hoc')
                    ->label('Ngày học')->displayFormat('d/m/Y')->native(false)
                    ->required()->columnSpan(3)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $d = \Carbon\Carbon::parse($state);
                            $set('tuan', (int) $d->isoWeek());
                            $set('nam', (int) $d->year);
                            $set('thang', (int) $d->month);
                        }
                    }),

                Forms\Components\TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')
                    ->seconds(false)->required()->columnSpan(2),

                Forms\Components\TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')
                    ->seconds(false)->required()->columnSpan(2),

                // Chuyên đề / Học phần lọc theo Chương trình của KhoaHoc
                Forms\Components\Select::make('chuyen_de_id')->label('Chuyên đề / Học phần')
                    ->options(function () {
                        $owner = $this->currentOwner();
                        if (!$owner?->chuong_trinh_id) return [];

                        // 1) ưu tiên pivot CT<->CD
                        $pivot = self::detectTable([
                            'chuong_trinh_chuyen_de',
                            'chuong_trinh_chuyen_des',
                            'chuong_trinhs_chuyen_des',
                            'chuong_trinh_has_chuyen_des',
                        ]);

                        $cdIds = [];
                        if ($pivot) {
                            $cdIds = DB::table($pivot)
                                ->where('chuong_trinh_id', $owner->chuong_trinh_id)
                                ->pluck('chuyen_de_id');
                        } elseif (Schema::hasColumn('chuyen_des','chuong_trinh_id')) {
                            // 2) fallback cột chuong_trinh_id ngay trên bảng chuyen_des
                            $cdIds = DB::table('chuyen_des')
                                ->where('chuong_trinh_id', $owner->chuong_trinh_id)
                                ->pluck('id');
                        }

                        $labelCol = Schema::hasColumn('chuyen_des','ten_chuyen_de')
                            ? 'ten_chuyen_de'
                            : (Schema::hasColumn('chuyen_des','ten') ? 'ten' : 'name');

                        $q = ChuyenDe::query()
                            ->when(!empty($cdIds), fn ($qq) => $qq->whereIn('id', $cdIds ?: [-1]))
                            ->orderBy($labelCol);

                        if (Schema::hasColumn('chuyen_des','trang_thai_tai_lieu')) {
                            $q->where('trang_thai_tai_lieu', 'Đang áp dụng');
                        }

                        return $q->get(['id', $labelCol])->mapWithKeys(
                            fn ($r) => [$r->id => (string) ($r->{$labelCol} ?? ('Chuyên đề #'.$r->id))]
                        )->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        if (!$value) return null;
                        $labelCol = Schema::hasColumn('chuyen_des','ten_chuyen_de')
                            ? 'ten_chuyen_de'
                            : (Schema::hasColumn('chuyen_des','ten') ? 'ten' : 'name');
                        $cd = ChuyenDe::find($value);
                        return $cd?->{$labelCol} ?? ('Chuyên đề #'.$value);
                    })
                    ->searchable()->preload()->required()->reactive()->columnSpan(5),

                // Giảng viên lọc theo CHUYÊN ĐỀ đã chọn
                Forms\Components\Select::make('giang_vien_id')->label('Giảng viên')
                    ->options(function (Forms\Get $get) {
                        $cdId = $get('chuyen_de_id');
                        if (!$cdId) return [];

                        // Tên pivot CD<->GV phổ biến
                        $pivotGV = self::detectTable([
                            'chuyen_de_giang_vien',
                            'chuyen_de_giang_viens',
                            'giang_vien_chuyen_de',
                            'giang_vien_chuyen_des',
                        ]);

                        $gvIds = $pivotGV
                            ? DB::table($pivotGV)->where('chuyen_de_id', $cdId)->pluck('giang_vien_id')
                            : [];

                        return GiangVien::query()
                            ->when(!empty($gvIds), fn ($q) => $q->whereIn('id', $gvIds ?: [-1]))
                            ->when(Schema::hasColumn('giang_viens','tinh_trang'), fn ($q) => $q->where('tinh_trang', 'Đang giảng dạy'))
                            ->orderBy('ho_ten')
                            ->get(['id','ho_ten'])
                            ->mapWithKeys(fn ($r) => [$r->id => (string) ($r->ho_ten ?? ('GV #'.$r->id))])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        if (!$value) return null;
                        $gv = GiangVien::find($value);
                        return $gv?->ho_ten ?? ('GV #'.$value);
                    })
                    ->searchable()->preload()->required()->columnSpan(4),

                // Địa điểm
                Forms\Components\Select::make('dia_diem_id')->label('Địa điểm đào tạo')
                    ->options(function () {
                        $label = Schema::hasColumn('dia_diem_dao_taos','ten_phong') ? 'ten_phong' : 'ma_phong';
                        return DiaDiemDaoTao::query()->orderBy($label)->get(['id','ten_phong','ma_phong'])
                            ->mapWithKeys(fn ($r) => [$r->id => (string) ($r->ten_phong ?? $r->ma_phong ?? ('Phòng #'.$r->id))])
                            ->toArray();
                    })
                    ->searchable()->preload()->columnSpan(4),

                Forms\Components\TextInput::make('so_bai_kiem_tra')->label('Số bài kiểm tra')
                    ->numeric()->default(0)->minValue(0)->columnSpan(2),

                Forms\Components\TextInput::make('so_gio_giang')->label('Số giờ giảng')
                    ->numeric()->minValue(0)->step(0.5)->columnSpan(2)
                    ->helperText('Có thể nhập tay (hỗ trợ .5 giờ); để trống hệ thống tự tính khi lưu.'),

                Forms\Components\TextInput::make('tuan')->label('Tuần')->disabled()->dehydrated()->columnSpan(2),
                Forms\Components\Hidden::make('thang'),
                Forms\Components\Hidden::make('nam'),
                Forms\Components\Toggle::make('bo_qua_trung_lich')
                    ->label('Ghi đè lịch trùng')
                    ->helperText('Chỉ bật khi bạn đã kiểm tra trùng phòng/GV và vẫn muốn lưu.')
                    ->default(false)
                    ->columnSpan(12),
            ]),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rowIndex')->label('TT')->rowIndex(),
                Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('gio_bat_dau')->label('Bắt đầu')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : ''),
                Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Kết thúc')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : ''),
                Tables\Columns\TextColumn::make('chuyen_de_text')->label('Chuyên đề/Học phần')
                    ->getStateUsing(function ($record) {
                        return $record->chuyenDe?->ten_chuyen_de
                            ?? $record->chuyenDe?->ten
                            ?? $record->chuyenDe?->name
                            ?? '';
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('giang_vien_text')->label('Giảng viên')
                    ->getStateUsing(fn ($record) => $record->giangVien?->ho_ten ?? ''),
                Tables\Columns\TextColumn::make('diaDiem.ten_phong')->label('Địa điểm'),
                Tables\Columns\TextColumn::make('so_bai_kiem_tra')->label('Số bài KT')->alignRight(),
                Tables\Columns\TextColumn::make('so_gio_giang')->label('Giờ giảng')->alignRight(),
                Tables\Columns\TextColumn::make('tuan')->label('Tuần')->alignRight(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Thêm giờ học')
                    ->modalHeading('Tạo lịch học')
                    ->modalSubmitActionLabel('Tạo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Sửa')->modalHeading('Sửa lịch học')->modalSubmitActionLabel('Lưu thay đổi'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->defaultSort('ngay_hoc', 'asc');
    }
}
