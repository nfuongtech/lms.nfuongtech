<?php

namespace App\Filament\Resources\KhoaHocResource\RelationManagers;

use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\DiaDiemDaoTao;
use App\Models\LichHoc;
use Filament\Forms;
use Filament\Tables;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LichHocsRelationManager extends RelationManager
{
    protected static string $relationship = 'lichHocs';
    protected static ?string $title = 'Lịch đào tạo';

    protected function normalizeTime($t, string $fallback = '00:00:00'): string
    {
        if ($t instanceof \DateTimeInterface) return $t->format('H:i:s');
        $t = trim((string) $t);
        if ($t === '') return $fallback;
        if (\strlen($t) === 4 || \strlen($t) === 5) return $t . ':00';
        return $t;
    }

    protected function detectConflicts(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        $ngay = $data['ngay_hoc'] ?? null;
        $gbd  = $this->normalizeTime($data['gio_bat_dau'] ?? null, '00:00:00');
        $gkt  = $this->normalizeTime($data['gio_ket_thuc'] ?? null, '23:59:59');

        if (! $ngay) return $errors;

        try {
            $start = Carbon::createFromFormat('H:i:s', $gbd);
            $end   = Carbon::createFromFormat('H:i:s', $gkt);
        } catch (\Throwable $e) {
            return $errors;
        }

        if ($end->lte($start)) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ bắt đầu.';
            return $errors;
        }
        if ($end->diffInMinutes($start) > 8 * 60) {
            $errors['gio_ket_thuc'] = 'Thời gian đào tạo liên tục không quá 8 tiếng.';
            return $errors;
        }

        $base = LichHoc::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereDate('ngay_hoc', $ngay instanceof \DateTimeInterface ? $ngay->format('Y-m-d') : $ngay)
            ->where('gio_bat_dau', '<', $gkt)
            ->where('gio_ket_thuc', '>', $gbd)
            ->whereHas('khoaHoc', function ($q) {
                $q->where('tam_hoan', false)
                  ->where('trang_thai', '!=', 'Kết thúc');
            });

        if (!empty($data['giang_vien_id'])) {
            if ((clone $base)->where('giang_vien_id', $data['giang_vien_id'])->exists()) {
                $errors['giang_vien_id'] = 'Lịch học trùng giảng viên và thời gian.';
                $errors['gio_bat_dau']   = $errors['gio_bat_dau']   ?? 'Thời gian bị trùng với lịch khác của giảng viên.';
                $errors['gio_ket_thuc']  = $errors['gio_ket_thuc']  ?? 'Thời gian bị trùng với lịch khác của giảng viên.';
            }
        }

        if (!empty($data['dia_diem_id']) || !empty($data['dia_diem'])) {
            $found = (clone $base)->where(function ($q) use ($data) {
                if (!empty($data['dia_diem_id'])) $q->orWhere('dia_diem_id', $data['dia_diem_id']);
                if (!empty($data['dia_diem']))    $q->orWhere('dia_diem', $data['dia_diem']);
            })->exists();

            if ($found) {
                $errors['dia_diem_id'] = 'Lịch học trùng phòng học và thời gian.';
                $errors['gio_bat_dau'] = $errors['gio_bat_dau'] ?? 'Thời gian bị trùng với lịch khác trong phòng.';
                $errors['gio_ket_thuc']= $errors['gio_ket_thuc'] ?? 'Thời gian bị trùng với lịch khác trong phòng.';
            }
        }

        return $errors;
    }

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
                        ->orderBy('ten_chuyen_de')->pluck('ten_chuyen_de', 'id');
                })
                ->searchable()->preload()->required()->reactive(),

            Forms\Components\Select::make('giang_vien_id')
                ->label('Giảng viên')
                ->options(function (Forms\Get $get) {
                    $cd = $get('chuyen_de_id');
                    if (! $cd) return [];
                    $gvIds = DB::table('chuyen_de_giang_vien')
                        ->where('chuyen_de_id', $cd)->pluck('giang_vien_id');
                    return GiangVien::whereIn('id', $gvIds)
                        ->orderBy('ho_ten')->pluck('ho_ten', 'id');
                })
                ->searchable()->preload()->required(),

            Forms\Components\Select::make('dia_diem_id')
                ->label('Địa điểm (phòng)')
                ->options(fn () => DiaDiemDaoTao::orderBy('ten_phong')->pluck('ten_phong','id'))
                ->searchable()->preload()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    $name = $state ? optional(DiaDiemDaoTao::find($state))->ten_phong : null;
                    if ($name) $set('dia_diem', $name);
                }),

            Forms\Components\Hidden::make('dia_diem'),

            Forms\Components\DatePicker::make('ngay_hoc')
                ->label('Ngày học')->required()
                ->native(false)->displayFormat('d/m/Y'),

            Forms\Components\TimePicker::make('gio_bat_dau')
                ->label('Giờ bắt đầu')->seconds(false)->required(),

            Forms\Components\TimePicker::make('gio_ket_thuc')
                ->label('Giờ kết thúc')->seconds(false)->required(),

            Forms\Components\TextInput::make('so_bai_kiem_tra')
                ->numeric()->default(0)->minValue(0)->label('Số bài kiểm tra'),

            // ✅ Cho phép số lẻ (step 0.1). BỎ helperText.
            Forms\Components\TextInput::make('so_gio_giang')
                ->label('Số giờ giảng')
                ->numeric()
                ->step(0.1)
                ->minValue(0.1)
                ->required(),

            Forms\Components\Toggle::make('force_override')
                ->label('Chấp nhận ghi đè khi trùng lịch')
                ->helperText('Đánh dấu để vẫn lưu nếu trùng giảng viên/phòng/giờ.')
                ->default(false)
                ->dehydrated(true),
        ])->columns(2);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('ngay_hoc')
            ->columns([
                Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('gio_bat_dau')->label('Bắt đầu')
                    ->formatStateUsing(fn ($state) => $state ? substr((string)$state,0,5) : ''),
                Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Kết thúc')
                    ->formatStateUsing(fn ($state) => $state ? substr((string)$state,0,5) : ''),
                Tables\Columns\TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề/Học phần')->wrap(),
                Tables\Columns\TextColumn::make('giangVien.ho_ten')->label('Giảng viên')->wrap(),
                Tables\Columns\TextColumn::make('so_gio_giang')->label('Số giờ giảng'),
                Tables\Columns\TextColumn::make('dia_diem')->label('Địa điểm')->wrap(),
                Tables\Columns\TextColumn::make('tuan')->label('Tuần'),
            ])
            ->emptyStateHeading('Không có lịch đào tạo')
            ->emptyStateDescription('Nhấn "Tạo lịch đào tạo" để thêm lịch.')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tạo lịch đào tạo')
                    ->modalHeading('Tạo lịch đào tạo')
                    ->modalSubmitActionLabel('Tạo')
                    ->createAnother(true)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['gio_bat_dau']  = $this->normalizeTime($data['gio_bat_dau'], '00:00:00');
                        $data['gio_ket_thuc'] = $this->normalizeTime($data['gio_ket_thuc'], '23:59:59');
                        return $data;
                    })
                    ->using(function (array $data, RelationManager $livewire) {
                        $owner = $livewire->getOwnerRecord(); // KhoaHoc
                        $data['khoa_hoc_id'] = $owner->getKey();

                        $errors = $this->detectConflicts($data, null);
                        if (!empty($errors) && empty($data['force_override'])) {
                            Notification::make()
                                ->title('Nội dung trùng')
                                ->body('Vui lòng sửa để không trùng hoặc đánh dấu "Chấp nhận ghi đè".')
                                ->danger()
                                ->send();
                            throw ValidationException::withMessages($errors);
                        }

                        $data['force_override'] = (bool) ($data['force_override'] ?? false);
                        return $livewire->getRelationship()->create($data);
                    })
                    ->successNotificationTitle('Đã tạo lịch học'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Sửa')
                    ->modalHeading('Sửa lịch học')
                    ->modalSubmitActionLabel('Lưu')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['gio_bat_dau']  = $this->normalizeTime($data['gio_bat_dau'], '00:00:00');
                        $data['gio_ket_thuc'] = $this->normalizeTime($data['gio_ket_thuc'], '23:59:59');
                        return $data;
                    })
                    ->using(function (Model $record, array $data) {
                        $errors = $this->detectConflicts($data, $record->getKey());
                        if (!empty($errors) && empty($data['force_override'])) {
                            Notification::make()
                                ->title('Nội dung trùng')
                                ->body('Vui lòng sửa để không trùng hoặc đánh dấu "Chấp nhận ghi đè".')
                                ->danger()
                                ->send();
                            throw ValidationException::withMessages($errors);
                        }
                        $data['force_override'] = (bool) ($data['force_override'] ?? false);
                        $record->fill($data)->save();
                        return $record;
                    })
                    ->successNotificationTitle('Đã lưu lịch học'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
            ]);
    }
}
