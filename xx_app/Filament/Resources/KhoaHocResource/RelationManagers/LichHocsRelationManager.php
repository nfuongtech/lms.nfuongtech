<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\ChuyenDe;
use App\Models\DiaDiemDaoTao;
use App\Models\GiangVien;
use App\Observers\ScheduleConflictService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LichHocsRelationManager extends RelationManager
{
    protected static string $relationship = 'lichHocs';
    protected static ?string $title = 'Tạo lịch học';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\DatePicker::make('ngay_hoc')
                    ->label('Ngày học')->displayFormat('d/m/Y')->native(false)
                    ->required()->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            try {
                                $dt = Carbon::parse($state);
                                $set('tuan',  (int)$dt->isoWeek());
                                $set('thang', (int)$dt->month);
                                $set('nam',   (int)$dt->year);
                            } catch (\Throwable $e) {}
                        }
                    })->columnSpan(2),

                Forms\Components\TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->seconds(false)->required(),
                Forms\Components\TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->seconds(false)->required(),
            ]),

            Forms\Components\Select::make('chuyen_de_id')
                ->label('Chuyên đề / Học phần')
                ->options(function () {
                    // TUYỆT ĐỐI KHÔNG dùng $this->getOwnerRecord()::class
                    $owner = $this->getOwnerRecord();
                    $q = ChuyenDe::query()->orderBy('ten_chuyen_de');

                    if ($owner?->chuong_trinh_id) {
                        $q->whereIn('id', function ($sub) use ($owner) {
                            $sub->from('chuong_trinh_chuyen_de')
                                ->select('chuyen_de_id')
                                ->where('chuong_trinh_id', $owner->chuong_trinh_id);
                        });
                    }

                    return $q->get(['id','ten_chuyen_de'])
                        ->mapWithKeys(fn ($cd) => [$cd->id => (string)$cd->ten_chuyen_de])
                        ->toArray();
                })
                ->searchable()->preload()->native(false)->required()->live(),

            Forms\Components\Select::make('giang_vien_id')
                ->label('Giảng viên')
                ->options(function (callable $get) {
                    $chuyenDeId = $get('chuyen_de_id');
                    $q = GiangVien::query()->orderByRaw('COALESCE(ho_ten,name) asc');

                    if ($chuyenDeId) {
                        $ids = DB::table('chuyen_de_giang_vien')
                            ->where('chuyen_de_id', $chuyenDeId)
                            ->pluck('giang_vien_id')
                            ->all();
                        $q->whereIn('id', $ids ?: [0]);
                    }

                    return $q->get(['id','ho_ten','name'])
                        ->mapWithKeys(function ($gv) {
                            $label = $gv->ho_ten ?? $gv->name ?? ('GV#'.$gv->id);
                            return [$gv->id => (string)$label];
                        })->toArray();
                })
                ->searchable()->preload()->native(false)->required(),

            Forms\Components\Select::make('dia_diem_id')
                ->label('Địa điểm')
                ->options(fn () =>
                    DiaDiemDaoTao::query()
                        ->orderBy('ten_phong')
                        ->get(['id','ten_phong','ma_phong'])
                        ->mapWithKeys(fn ($r) => [$r->id => (string)($r->ten_phong ?? $r->ma_phong ?? 'Phòng #'.$r->id)])
                        ->toArray()
                )
                ->searchable()->preload()->native(false)->required(),

            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('so_bai_kiem_tra')->label('Số bài kiểm tra')->numeric()->default(0),

                Forms\Components\TextInput::make('so_gio_giang')
                    ->label('Số giờ giảng')->numeric()->step('0.1')->minValue(0)
                    ->helperText('Để trống sẽ tự tính theo giờ bắt đầu/kết thúc'),

                Forms\Components\TextInput::make('tuan')->label('Tuần (ISO)')->numeric()->disabled()->dehydrated(true),

                Forms\Components\Checkbox::make('bo_qua_trung_lich')
                    ->label('Ghi đè khi trùng (bỏ qua cơ chế trùng)')
                    ->default(false),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('gio_bat_dau')->label('Bắt đầu'),
                Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Kết thúc'),
                Tables\Columns\TextColumn::make('tuan')->label('Tuần')->sortable(),
                Tables\Columns\TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề'),
                Tables\Columns\TextColumn::make('giangVien.ho_ten')
                    ->label('Giảng viên')
                    ->formatStateUsing(fn ($record) => $record->giangVien?->ho_ten ?? $record->giangVien?->name ?? ''),
                Tables\Columns\TextColumn::make('diaDiemDaoTao.ten_phong')->label('Địa điểm'),
                Tables\Columns\TextColumn::make('so_bai_kiem_tra')->label('Số bài KT'),
                Tables\Columns\TextColumn::make('so_gio_giang')->label('Số giờ'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tạo lịch học')
                    ->modalSubmitActionLabel('Tạo')
                    ->createAnother(true)
                    ->mutateFormDataUsing(function (array $data) {
                        if (!empty($data['ngay_hoc'])) {
                            try {
                                $d = Carbon::parse($data['ngay_hoc']);
                                $data['tuan']  = $data['tuan']  ?? (int)$d->isoWeek();
                                $data['thang'] = $data['thang'] ?? (int)$d->month;
                                $data['nam']   = $data['nam']   ?? (int)$d->year;
                            } catch (\Throwable $e) {}
                        }
                        if (empty($data['so_gio_giang']) && !empty($data['gio_bat_dau']) && !empty($data['gio_ket_thuc'])) {
                            try {
                                $s = Carbon::createFromFormat('H:i', substr($data['gio_bat_dau'], 0, 5));
                                $e = Carbon::createFromFormat('H:i', substr($data['gio_ket_thuc'], 0, 5));
                                $mins = max(0, $e->diffInMinutes($s));
                                $data['so_gio_giang'] = round($mins / 60, 1);
                            } catch (\Throwable $e) {}
                        }
                        return $data;
                    })
                    ->beforeCreate(function (array $data) {
                        $owner = $this->getOwnerRecord();
                        if ($owner?->tam_hoan) return; // KH tạm hoãn: bỏ qua check trùng

                        $service = new ScheduleConflictService();
                        $conflicts = $service->detectConflicts([
                            'ngay_hoc'      => $data['ngay_hoc'] ?? null,
                            'gio_bat_dau'   => $data['gio_bat_dau'] ?? null,
                            'gio_ket_thuc'  => $data['gio_ket_thuc'] ?? null,
                            'giang_vien_id' => $data['giang_vien_id'] ?? null,
                            'dia_diem_id'   => $data['dia_diem_id'] ?? null,
                            'khoa_hoc_id'   => $owner?->id,
                        ]);

                        $override = (bool)($data['bo_qua_trung_lich'] ?? false);

                        if ($conflicts->isNotEmpty() && !$override) {
                            $msg = "Phát hiện trùng lịch:\n";
                            foreach ($conflicts as $c) {
                                $loai = $c['type'] === 'giang_vien' ? 'Giảng viên' : 'Phòng học';
                                $msg .= "- {$loai}: {$c['ngay']} {$c['gio']} (KH {$c['ma_khoa_hoc']} - {$c['ten_khoa_hoc']})\n";
                            }
                            throw ValidationException::withMessages([
                                'bo_qua_trung_lich' => $msg."Bạn có thể tick 'Ghi đè khi trùng (bỏ qua cơ chế trùng)' để lưu.",
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Sửa')->modalSubmitActionLabel('Lưu'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->defaultSort('ngay_hoc', 'asc');
    }
}
