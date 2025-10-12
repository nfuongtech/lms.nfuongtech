<?php

namespace App\Filament\Pages;

<<<<<<< HEAD
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HocVienKhongHoanThanhPage extends Page
{
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';
    protected static ?string $title = 'Học viên không hoàn thành';
    protected static string $view = 'filament.pages.hoc-vien-khong-hoan-thanh';

    public $availableNams = [];
    public $selectedNam;
    public $availableWeeks = [];
    public $selectedTuan;
    public $fromDate;
    public $toDate;
    public $availableCourses = [];
    public $selectedKhoaHoc;

    public $courseRows = [];
    public $hocVienRows = [];

    public $showEditModal = false;
    public $showApproveModal = false;

    public $editRecordId = null;
    public $approveRecordId = null;

    public $editForm = [
        'ly_do_khong_hoan_thanh' => null,
        'co_the_ghi_danh_lai' => false,
    ];

    public function mount(): void
    {
        $this->selectedNam = now()->year;
        $this->loadSelectableOptions();
        $this->refreshData();
    }
=======
use Filament\Pages\Page;
use Illuminate\Support\Facades\Route;

class HocVienKhongHoanThanhPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Học viên không hoàn thành';
    protected static string $view = 'filament.pages.simple-redirect';
    protected static bool $shouldRegisterNavigation = false;
>>>>>>> origin/codex/update-attendance-page-functionality-tbtb2h

    public static function getSlug(): string
    {
        return 'hoc-vien-khong-hoan-thanhs';
    }

<<<<<<< HEAD
    public function updatedSelectedNam(): void
    {
        $this->selectedTuan = null;
        $this->selectedKhoaHoc = null;
        $this->loadSelectableOptions();
        $this->refreshData();
    }

    public function updatedSelectedTuan(): void
    {
        $this->selectedKhoaHoc = null;
        $this->loadSelectableOptions(false);
        $this->refreshData();
    }

    public function updatedFromDate(): void
    {
        $this->loadSelectableOptions(false);
        $this->refreshData();
    }

    public function updatedToDate(): void
    {
        $this->loadSelectableOptions(false);
        $this->refreshData();
    }

    public function updatedSelectedKhoaHoc(): void
    {
        $this->refreshData();
    }

    public function openEditModal(int $recordId): void
    {
        $record = HocVienKhongHoanThanh::find($recordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $this->editRecordId = $recordId;
        $this->editForm = [
            'ly_do_khong_hoan_thanh' => $record->ly_do_khong_hoan_thanh,
            'co_the_ghi_danh_lai' => (bool) $record->co_the_ghi_danh_lai,
        ];

        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        if (! $this->editRecordId) {
            return;
        }

        $data = $this->validate([
            'editForm.ly_do_khong_hoan_thanh' => 'nullable|string|max:500',
            'editForm.co_the_ghi_danh_lai' => 'boolean',
        ])['editForm'];

        $record = HocVienKhongHoanThanh::find($this->editRecordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $record->update($data);
        $this->showEditModal = false;
        $this->editRecordId = null;
        $this->refreshData();

        Notification::make()->title('Đã cập nhật thông tin học viên.')->success()->send();
    }

    public function confirmApprove(int $recordId): void
    {
        $this->approveRecordId = $recordId;
        $this->showApproveModal = true;
    }

    public function approveRecord(): void
    {
        if (! $this->approveRecordId) {
            return;
        }

        $record = HocVienKhongHoanThanh::with('ketQua')->find($this->approveRecordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $ketQua = $record->ketQua;
        if ($ketQua) {
            $ketQua->ket_qua = 'hoan_thanh';
            $ketQua->ket_qua_goi_y = $ketQua->ket_qua_goi_y ?? 'hoan_thanh';
            $ketQua->can_hoc_lai = false;
            $ketQua->save();
        }

        HocVienHoanThanh::updateOrCreate(
            [
                'hoc_vien_id' => $record->hoc_vien_id,
                'khoa_hoc_id' => $record->khoa_hoc_id,
                'ket_qua_khoa_hoc_id' => $record->ket_qua_khoa_hoc_id,
            ],
            [
                'da_duyet' => false,
            ]
        );

        $record->delete();

        $this->showApproveModal = false;
        $this->approveRecordId = null;
        $this->refreshData();

        Notification::make()->title('Đã chuyển học viên sang danh sách hoàn thành.')->success()->send();
    }

    private function loadSelectableOptions(bool $resetWeeks = true): void
    {
        $namQuery = LichHoc::query()->select('nam')->distinct()->orderByDesc('nam');
        $this->availableNams = $namQuery->pluck('nam')->toArray();

        if (! $this->selectedNam && ! empty($this->availableNams)) {
            $this->selectedNam = $this->availableNams[0];
        }

        if ($resetWeeks) {
            $this->availableWeeks = $this->getAvailableWeeks();
            if ($this->selectedTuan && ! in_array($this->selectedTuan, $this->availableWeeks, true)) {
                $this->selectedTuan = null;
            }
        }

        $this->availableCourses = $this->getAvailableCourses();
        if ($this->selectedKhoaHoc && ! $this->availableCourses->contains('id', $this->selectedKhoaHoc)) {
            $this->selectedKhoaHoc = null;
        }
    }

    private function getAvailableWeeks(): array
    {
        if (! $this->selectedNam) {
            return [];
        }

        return LichHoc::query()
            ->where('nam', $this->selectedNam)
            ->select('tuan')
            ->distinct()
            ->orderBy('tuan')
            ->pluck('tuan')
            ->toArray();
    }

    private function getAvailableCourses(): Collection
    {
        $courseIds = $this->resolveCourseIds();
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return KhoaHoc::with('chuongTrinh')
            ->whereIn('id', $courseIds)
            ->orderBy('ma_khoa_hoc')
            ->get();
    }

    private function resolveCourseIds(): Collection
    {
        $query = LichHoc::query();

        if ($this->selectedNam) {
            $query->where('nam', $this->selectedNam);
        }

        if ($this->selectedTuan) {
            $query->where('tuan', $this->selectedTuan);
        }

        if ($this->fromDate) {
            $query->whereDate('ngay_hoc', '>=', $this->fromDate);
        }

        if ($this->toDate) {
            $query->whereDate('ngay_hoc', '<=', $this->toDate);
        }

        return $query->pluck('khoa_hoc_id')->unique()->map(fn ($id) => (int) $id);
    }

    private function refreshData(): void
    {
        $courseIds = $this->resolveCourseIds();
        if ($this->selectedKhoaHoc) {
            $courseIds = $courseIds->intersect([(int) $this->selectedKhoaHoc]);
        }

        if ($courseIds->isEmpty()) {
            $this->courseRows = [];
            $this->hocVienRows = [];
            return;
        }

        $courses = KhoaHoc::with([
            'chuongTrinh',
            'lichHocs' => function ($query) {
                if ($this->selectedNam) {
                    $query->where('nam', $this->selectedNam);
                }
                if ($this->selectedTuan) {
                    $query->where('tuan', $this->selectedTuan);
                }
                if ($this->fromDate) {
                    $query->whereDate('ngay_hoc', '>=', $this->fromDate);
                }
                if ($this->toDate) {
                    $query->whereDate('ngay_hoc', '<=', $this->toDate);
                }
                $query->orderBy('ngay_hoc');
            },
            'lichHocs.giangVien',
        ])
            ->whereIn('id', $courseIds)
            ->withCount('hocVienKhongHoanThanhRecords')
            ->get();

        $courseRows = [];
        $courseMap = [];
        foreach ($courses as $course) {
            $lichHocs = $course->lichHocs;
            if ($lichHocs->isEmpty()) {
                continue;
            }

            $dates = $lichHocs->map(function ($lich) {
                if ($lich->ngay_hoc instanceof \DateTimeInterface) {
                    return $lich->ngay_hoc->format('d/m/Y');
                }
                return $lich->ngay_hoc ? date('d/m/Y', strtotime((string) $lich->ngay_hoc)) : null;
            })->filter();

            $courseRows[] = [
                'id' => $course->id,
                'ma_khoa_hoc' => $course->ma_khoa_hoc ?? '',
                'ten_khoa_hoc' => $course->chuongTrinh->ten_chuong_trinh ?? $course->ten_khoa_hoc,
                'so_buoi' => $lichHocs->count(),
                'tuan' => $lichHocs->pluck('tuan')->unique()->filter()->implode(', '),
                'ngay_dao_tao' => $this->formatNgayDaoTao($dates),
                'giang_vien' => $lichHocs->map(fn ($lich) => $lich->giangVien?->ho_ten)->filter()->unique()->implode(', '),
                'so_luong_khong_hoan_thanh' => $course->hoc_vien_khong_hoan_thanh_records_count,
            ];

            $courseMap[$course->id] = [
                'ngay_dao_tao' => $this->formatNgayDaoTao($dates),
            ];
        }

        $this->courseRows = $courseRows;

        $hocVienQuery = HocVienKhongHoanThanh::with([
            'hocVien.donVi',
            'khoaHoc',
            'ketQua.dangKy.diemDanhs.lichHoc',
        ])->whereIn('khoa_hoc_id', array_keys($courseMap));

        if ($this->selectedKhoaHoc) {
            $hocVienQuery->where('khoa_hoc_id', $this->selectedKhoaHoc);
        }

        $records = $hocVienQuery->orderBy('hoc_vien_id')->get();

        $rows = [];
        foreach ($records as $record) {
            $ketQua = $record->ketQua;
            $attendance = $ketQua?->dangKy?->diemDanhs ?? collect();

            $rows[] = [
                'id' => $record->id,
                'ma_so' => $record->hocVien->msnv ?? '',
                'ho_ten' => $record->hocVien->ho_ten ?? '',
                'ngay_sinh' => optional($record->hocVien->ngay_sinh)->format('d/m/Y'),
                'gioi_tinh' => $this->formatGender($record->hocVien->gioi_tinh ?? null),
                'chuc_vu' => $record->hocVien->chuc_vu ?? '—',
                'don_vi' => $record->hocVien->donVi->ten_hien_thi ?? '—',
                'thoi_gian' => $courseMap[$record->khoa_hoc_id]['ngay_dao_tao'] ?? '',
                'ly_do' => $record->ly_do_khong_hoan_thanh ?? '—',
                'co_the_ghi_danh_lai' => (bool) $record->co_the_ghi_danh_lai,
                'danh_gia_ren_luyen' => $ketQua?->danh_gia_ren_luyen,
                'diem_trung_binh' => $ketQua?->diem_trung_binh ?? $ketQua?->diem,
                'chuyen_can_diem' => $this->formatAttendanceSummary($attendance),
            ];
        }

        $this->hocVienRows = $rows;
    }

    private function formatNgayDaoTao(Collection $dates): string
    {
        if ($dates->isEmpty()) {
            return '';
        }

        if ($dates->count() <= 3) {
            return $dates->implode(', ');
        }

        return $dates->first() . ' - ' . $dates->last();
    }

    private function formatAttendanceSummary(Collection $attendance): string
    {
        if ($attendance->isEmpty()) {
            return '';
        }

        return $attendance->map(function ($item) {
            $lich = $item->lichHoc;
            $date = $lich?->ngay_hoc instanceof \DateTimeInterface
                ? $lich->ngay_hoc->format('d/m')
                : ($lich?->ngay_hoc ? date('d/m', strtotime((string) $lich->ngay_hoc)) : '—');

            $statusLabel = match ($item->trang_thai) {
                'vang_phep' => 'Vắng P',
                'vang_khong_phep' => 'Vắng KP',
                default => 'Có mặt',
            };

            $score = $item->diem_buoi_hoc !== null ? number_format((float) $item->diem_buoi_hoc, 2) : '—';

            return $date . ': ' . $statusLabel . ' (Điểm: ' . $score . ')';
        })->implode("\n");
    }

    private function formatGender(?string $value): string
    {
        $value = Str::lower((string) $value);
        return match ($value) {
            'nu', 'nữ', 'female', 'f' => 'Nữ',
            default => 'Nam',
        };
    }

=======
    public function mount(): void
    {
        if (Route::has('filament.admin.resources.hoc-vien-khong-hoan-thanhs.index')) {
            $this->redirectRoute('filament.admin.resources.hoc-vien-khong-hoan-thanhs.index');
        }
    }
>>>>>>> origin/codex/update-attendance-page-functionality-tbtb2h
}
