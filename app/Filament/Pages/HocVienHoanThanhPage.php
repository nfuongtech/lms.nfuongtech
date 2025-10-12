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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class HocVienHoanThanhPage extends Page
{
    use WithFileUploads;

    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';
    protected static ?string $title = 'Học viên hoàn thành';
    protected static string $view = 'filament.pages.hoc-vien-hoan-thanh';

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
    public $showSupplementModal = false;
    public $showRejectModal = false;

    public $editRecordId = null;
    public $supplementRecordId = null;
    public $rejectRecordId = null;

    public $editForm = [
        'danh_gia_ren_luyen' => null,
        'ghi_chu' => null,
    ];

    public $supplementForm = [
        'chi_phi_dao_tao' => null,
        'chung_chi_da_cap' => false,
        'chung_chi_link' => null,
        'chung_chi_tap_tin' => null,
        'so_chung_nhan' => null,
        'chung_chi_het_han' => null,
    ];

    public $rejectReason = null;

    protected array $ketQuaColumnCache = [];

    public function mount(): void
    {
        $this->selectedNam = now()->year;
        $this->loadSelectableOptions();
        $this->refreshData();
    }
=======
use Filament\Pages\Page;
use Illuminate\Support\Facades\Route;

class HocVienHoanThanhPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Học viên hoàn thành';
    protected static string $view = 'filament.pages.simple-redirect';
    protected static bool $shouldRegisterNavigation = false;
>>>>>>> origin/codex/update-attendance-page-functionality-tbtb2h

    public static function getSlug(): string
    {
        return 'hoc-vien-hoan-thanhs';
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
        $record = HocVienHoanThanh::with('ketQua')->find($recordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $this->editRecordId = $recordId;
        $this->editForm = [
            'danh_gia_ren_luyen' => $record->ketQua?->danh_gia_ren_luyen,
            'ghi_chu' => $record->ghi_chu,
        ];

        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        if (! $this->editRecordId) {
            return;
        }

        $data = $this->validate([
            'editForm.danh_gia_ren_luyen' => 'nullable|string|max:500',
            'editForm.ghi_chu' => 'nullable|string|max:500',
        ])['editForm'];

        $record = HocVienHoanThanh::with('ketQua')->find($this->editRecordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        if ($record->ketQua && $this->ketQuaHasColumn('danh_gia_ren_luyen')) {
            $record->ketQua->danh_gia_ren_luyen = $data['danh_gia_ren_luyen'];
            $record->ketQua->save();
        }

        $record->update([
            'ghi_chu' => $data['ghi_chu'],
        ]);

        $this->showEditModal = false;
        $this->editRecordId = null;
        $this->refreshData();

        Notification::make()->title('Đã cập nhật ghi chú và đánh giá.')->success()->send();
    }

    public function approveRecord(int $recordId): void
    {
        $record = HocVienHoanThanh::with('ketQua')->find($recordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $record->da_duyet = true;
        $record->ngay_duyet = now();
        $record->save();

        if ($record->ketQua) {
            $record->ketQua->ket_qua = 'hoan_thanh';
            $record->ketQua->can_hoc_lai = false;
            $record->ketQua->save();
        }

        Notification::make()->title('Đã duyệt kết quả học viên.')->success()->send();
        $this->refreshData();
    }

    public function openSupplementModal(int $recordId): void
    {
        $record = HocVienHoanThanh::find($recordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $this->supplementRecordId = $recordId;
        $this->supplementForm = [
            'chi_phi_dao_tao' => $record->chi_phi_dao_tao,
            'chung_chi_da_cap' => (bool) $record->chung_chi_da_cap,
            'chung_chi_link' => $record->chung_chi_link,
            'chung_chi_tap_tin' => null,
            'so_chung_nhan' => $record->so_chung_nhan,
            'chung_chi_het_han' => optional($record->chung_chi_het_han)->format('Y-m-d'),
        ];

        $this->showSupplementModal = true;
    }

    public function saveSupplement(): void
    {
        if (! $this->supplementRecordId) {
            return;
        }

        $data = $this->validate([
            'supplementForm.chi_phi_dao_tao' => 'nullable|numeric|min:0',
            'supplementForm.chung_chi_da_cap' => 'boolean',
            'supplementForm.chung_chi_link' => 'nullable|string|max:255',
            'supplementForm.chung_chi_tap_tin' => 'nullable|file|mimes:pdf|max:5120',
            'supplementForm.so_chung_nhan' => 'nullable|string|max:255',
            'supplementForm.chung_chi_het_han' => 'nullable|date',
        ])['supplementForm'];

        $record = HocVienHoanThanh::find($this->supplementRecordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $updateData = [
            'chi_phi_dao_tao' => $data['chi_phi_dao_tao'],
            'chung_chi_da_cap' => $data['chung_chi_da_cap'],
            'chung_chi_link' => $data['chung_chi_link'],
            'so_chung_nhan' => $data['so_chung_nhan'],
            'chung_chi_het_han' => $data['chung_chi_het_han'],
        ];

        if (! empty($data['chung_chi_tap_tin'])) {
            $path = $data['chung_chi_tap_tin']->store('chung-chi', ['disk' => 'public']);
            $updateData['chung_chi_tap_tin'] = $path;
        }

        $record->update($updateData);

        $this->showSupplementModal = false;
        $this->supplementRecordId = null;
        $this->refreshData();

        Notification::make()->title('Đã cập nhật thông tin chứng chỉ.')->success()->send();
    }

    public function confirmReject(int $recordId): void
    {
        $this->rejectRecordId = $recordId;
        $this->rejectReason = null;
        $this->showRejectModal = true;
    }

    public function rejectRecord(): void
    {
        if (! $this->rejectRecordId) {
            return;
        }

        $record = HocVienHoanThanh::with('ketQua')->find($this->rejectRecordId);
        if (! $record) {
            Notification::make()->title('Không tìm thấy học viên.')->danger()->send();
            return;
        }

        $data = $this->validate([
            'rejectReason' => 'nullable|string|max:500',
        ]);

        $ketQua = $record->ketQua;
        if ($ketQua) {
            $ketQua->ket_qua = 'khong_hoan_thanh';
            $ketQua->ket_qua_goi_y = $ketQua->ket_qua_goi_y ?? 'khong_hoan_thanh';
            $ketQua->can_hoc_lai = true;
            $ketQua->save();
        }

        HocVienKhongHoanThanh::updateOrCreate(
            [
                'hoc_vien_id' => $record->hoc_vien_id,
                'khoa_hoc_id' => $record->khoa_hoc_id,
                'ket_qua_khoa_hoc_id' => $record->ket_qua_khoa_hoc_id,
            ],
            [
                'ly_do_khong_hoan_thanh' => $data['rejectReason'],
                'co_the_ghi_danh_lai' => false,
            ]
        );

        $record->delete();

        $this->showRejectModal = false;
        $this->rejectRecordId = null;
        $this->refreshData();

        Notification::make()->title('Đã chuyển học viên sang trạng thái không hoàn thành.')->warning()->send();
    }

    public function exportSummary()
    {
        $this->refreshData();
        $filename = 'khoa-hoc-hoan-thanh-' . now()->format('Ymd_His') . '.csv';

        $rows = $this->courseRows;
        $headers = [
            'STT',
            'Mã khóa',
            'Tên khóa học',
            'Tổng số giờ',
            'Số buổi',
            'Tuần',
            'Ngày đào tạo',
            'Giảng viên',
            'SL học viên đăng ký',
            'SL học viên hoàn thành',
        ];

        $callback = function () use ($rows, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['ma_khoa_hoc'] ?? '',
                    $row['ten_khoa_hoc'] ?? '',
                    $row['tong_so_gio'] ?? '',
                    $row['so_buoi'] ?? '',
                    $row['tuan'] ?? '',
                    $row['ngay_dao_tao'] ?? '',
                    $row['giang_vien'] ?? '',
                    $row['so_luong_hv'] ?? '',
                    $row['so_luong_hoan_thanh'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
            ->withCount('dangKies')
            ->get();

        $courseMap = [];
        $courseRows = [];

        foreach ($courses as $course) {
            $lichHocs = $course->lichHocs;
            if ($lichHocs->isEmpty()) {
                continue;
            }

            $tongGio = (float) $lichHocs->sum(fn ($item) => (float) ($item->so_gio_giang ?? 0));
            $dates = $lichHocs->map(function ($lich) {
                if ($lich->ngay_hoc instanceof \DateTimeInterface) {
                    return $lich->ngay_hoc->format('d/m/Y');
                }

                return $lich->ngay_hoc ? date('d/m/Y', strtotime((string) $lich->ngay_hoc)) : null;
            })->filter();

            $ngayDaoTao = $this->formatNgayDaoTao($dates);
            $giangVien = $lichHocs->map(fn ($lich) => $lich->giangVien?->ho_ten)->filter()->unique()->implode(', ');

            $hoanThanhCount = HocVienHoanThanh::where('khoa_hoc_id', $course->id)->count();

            $courseRows[] = [
                'id' => $course->id,
                'ma_khoa_hoc' => $course->ma_khoa_hoc ?? '',
                'ten_khoa_hoc' => $course->chuongTrinh->ten_chuong_trinh ?? $course->ten_khoa_hoc,
                'tong_so_gio' => number_format($tongGio, 2),
                'so_buoi' => $lichHocs->count(),
                'tuan' => $lichHocs->pluck('tuan')->unique()->filter()->implode(', '),
                'ngay_dao_tao' => $ngayDaoTao,
                'giang_vien' => $giangVien,
                'so_luong_hv' => $course->dang_kies_count,
                'so_luong_hoan_thanh' => $hoanThanhCount,
            ];

            $courseMap[$course->id] = [
                'lich_hocs' => $lichHocs,
                'ngay_dao_tao' => $ngayDaoTao,
                'dates' => $dates,
            ];
        }

        $this->courseRows = $courseRows;

        $hocVienQuery = HocVienHoanThanh::with([
            'hocVien.donVi',
            'khoaHoc',
            'ketQua.dangKy.diemDanhs.lichHoc',
        ])->whereIn('khoa_hoc_id', array_keys($courseMap));

        if ($this->selectedKhoaHoc) {
            $hocVienQuery->where('khoa_hoc_id', $this->selectedKhoaHoc);
        }

        $hocVienRecords = $hocVienQuery->orderByDesc('da_duyet')->orderBy('hoc_vien_id')->get();

        $rows = [];
        foreach ($hocVienRecords as $record) {
            $ketQua = $record->ketQua;
            $dangKy = $ketQua?->dangKy;
            $attendance = $dangKy?->diemDanhs ?? collect();
            $attendanceSummary = $this->formatAttendanceSummary($attendance);

            $tongGio = $ketQua?->tong_so_gio_thuc_te;
            if ($tongGio === null && $this->ketQuaHasColumn('diem')) {
                $tongGio = $this->calculateAttendanceHours($attendance);
            }

            $rows[] = [
                'id' => $record->id,
                'ma_so' => $record->hocVien->msnv ?? '',
                'ho_ten' => $record->hocVien->ho_ten ?? '',
                'ngay_sinh' => optional($record->hocVien->ngay_sinh)->format('d/m/Y'),
                'gioi_tinh' => $this->formatGender($record->hocVien->gioi_tinh ?? null),
                'chuc_vu' => $record->hocVien->chuc_vu ?? '',
                'don_vi' => $record->hocVien->donVi->ten_hien_thi ?? '',
                'ngay_hoan_thanh' => optional($record->ngay_hoan_thanh)->format('d/m/Y'),
                'so_gio_hoc' => $tongGio !== null ? number_format((float) $tongGio, 2) : '—',
                'thoi_gian' => $courseMap[$record->khoa_hoc_id]['ngay_dao_tao'] ?? '',
                'chuyen_can_diem' => $attendanceSummary,
                'diem_trung_binh' => $ketQua?->diem_trung_binh ?? $ketQua?->diem,
                'danh_gia_ren_luyen' => $ketQua?->danh_gia_ren_luyen,
                'ket_qua' => $ketQua?->ket_qua ?? 'hoan_thanh',
                'da_duyet' => (bool) $record->da_duyet,
                'ngay_duyet' => optional($record->ngay_duyet)->format('d/m/Y H:i'),
            ];
        }

        $this->hocVienRows = $rows;
    }

    private function calculateAttendanceHours(Collection $attendance): float
    {
        return (float) $attendance->sum(function ($item) {
            if (($item->trang_thai ?? 'co_mat') !== 'co_mat') {
                return 0;
            }

            return (float) ($item->so_gio_hoc ?? 0);
        });
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

    private function formatGender(?string $value): string
    {
        $value = Str::lower((string) $value);
        return match ($value) {
            'nu', 'nữ', 'female', 'f' => 'Nữ',
            default => 'Nam',
        };
    }

    private function ketQuaHasColumn(string $column): bool
    {
        if (! array_key_exists('__columns', $this->ketQuaColumnCache)) {
            try {
                $columns = Schema::getColumnListing('ket_qua_khoa_hocs');
            } catch (\Throwable $exception) {
                $columns = [];
            }

            $this->ketQuaColumnCache['__columns'] = array_fill_keys($columns, true);
        }

        return isset($this->ketQuaColumnCache['__columns'][$column]);
=======
    public function mount(): void
    {
        if (Route::has('filament.admin.resources.hoc-vien-hoan-thanhs.index')) {
            $this->redirectRoute('filament.admin.resources.hoc-vien-hoan-thanhs.index');
        }
>>>>>>> origin/codex/update-attendance-page-functionality-tbtb2h
    }
}
