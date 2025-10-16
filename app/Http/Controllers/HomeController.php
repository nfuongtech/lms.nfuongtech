<?php

namespace App\Http\Controllers;

use App\Models\HocVien;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $yearParam  = $request->query('year');
        $weekParam  = $request->query('week');
        $monthParam = $request->query('month');

        $hasWeek  = $request->has('week')  && $weekParam  !== '' && $weekParam  !== null;
        $hasMonth = $request->has('month') && $monthParam !== '' && $monthParam !== null;
        $hasYear  = $request->has('year')  && $yearParam  !== '' && $yearParam  !== null;

        if ($hasWeek || (!$hasWeek && !$hasMonth && !$hasYear)) {
            $mode  = 'week';
            $year  = (int) ($hasYear ? $yearParam : now()->year);
            $week  = (int) ($hasWeek ? $weekParam : now()->isoWeek());
            $month = null;
        } elseif ($hasMonth) {
            $mode  = 'month';
            $year  = (int) ($hasYear ? $yearParam : now()->year);
            $month = (int) $monthParam;
            $week  = (int) now()->isoWeek();
        } else {
            $mode  = 'year';
            $year  = (int) ($hasYear ? $yearParam : now()->year);
            $week  = (int) now()->isoWeek();
            $month = null;
        }

        $records = KhoaHoc::query()
            ->where('nam', $year)
            ->when($mode === 'week',  fn ($q) => $q->whereHas('lichHocs', fn ($h) => $h->where('tuan',  $week)))
            ->when($mode === 'month', fn ($q) => $q->whereHas('lichHocs', fn ($h) => $h->where('thang', $month)))
            ->with([
                'lichHocs.giangVien' => fn ($q) => $q->select(['id', 'ho_ten']),
                'lichHocs.diaDiem'   => fn ($q) => $q->select(['id', 'ten_phong', 'ma_phong']),
            ])
            ->withCount(['dangKies as registered_students_count'])
            ->get();

        $prepared = $records->map(function ($kh) use ($mode, $week, $month) {
            $lichFiltered = $kh->lichHocs
                ->filter(function ($lh) use ($mode, $week, $month) {
                    if ($mode === 'week') {
                        return (int) $lh->tuan === (int) $week;
                    }
                    if ($mode === 'month') {
                        return (int) $lh->thang === (int) $month;
                    }
                    return true;
                });

            $latest = $lichFiltered->map(function ($lh) {
                $day = $lh->ngay_hoc instanceof \DateTimeInterface
                    ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                    : Carbon::parse($lh->ngay_hoc)->startOfDay();

                $end   = $lh->gio_ket_thuc ? (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc) : null;
                $start = $lh->gio_bat_dau  ? (clone $day)->setTimeFromTimeString($lh->gio_bat_dau)  : null;

                return $end ?? $start ?? $day;
            })->filter();

            $latestDt = $latest->count() ? $latest->max() : Carbon::create(1970, 1, 1, 0, 0, 0);

            return [
                'record'       => $kh,
                'lichFiltered' => $lichFiltered
                    ->sortBy([
                        ['ngay_hoc', 'asc'],
                        ['gio_bat_dau', 'asc'],
                    ])
                    ->values(),
                'latest'       => $latestDt,
            ];
        })->sortByDesc('latest')->values();

        $today = Carbon::today();

        $rows = $prepared->map(function ($p) use ($mode, $week, $today) {
            /** @var \App\Models\KhoaHoc $kh */
            $kh = $p['record'];
            $lichFiltered = $p['lichFiltered'];

            $giangViens = $lichFiltered
                ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $giangVienText = ! empty($giangViens) ? implode(', ', $giangViens) : '—';

            $ngayGioLines = $lichFiltered->map(function ($lh) use ($today) {
                $date    = Carbon::parse($lh->ngay_hoc);
                $dateStr = $date->format('d/m/Y');
                $start   = $lh->gio_bat_dau  ? substr($lh->gio_bat_dau, 0, 5)  : '';
                $end     = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                $line    = "{$dateStr}, {$start}-{$end}";

                if ($date->isSameDay($today)) {
                    return '<span class="session today">' . $line . '</span>';
                }

                return '<span class="session">' . $line . '</span>';
            })->all();
            $ngayGioHtml = $ngayGioLines ? implode('<br>', $ngayGioLines) : '—';

            $diaDiemLines = $lichFiltered->map(function ($lh) {
                $dd = $lh->diaDiem;
                return $dd?->ten_phong ?? $dd?->ma_phong ?? '';
            })->all();
            $diaDiemHtml = $diaDiemLines ? implode('<br>', $diaDiemLines) : '—';

            $primarySession = $this->summarizePrimarySession($lichFiltered);

            $weeks = $lichFiltered->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');
            if ($weeks === '') {
                $weeks = $mode === 'week' ? (string) $week : '—';
            }

            $status = $kh->trang_thai_hien_thi;
            $statusSlug = Str::slug($status) ?: 'trang-thai';

            return [
                'id'                          => $kh->id,
                'ma_khoa_hoc'                 => $kh->ma_khoa_hoc,
                'ten_khoa_hoc'                => $kh->ten_khoa_hoc,
                'giang_vien'                  => $giangVienText,
                'ngay_gio_html'               => $ngayGioHtml,
                'dia_diem_html'               => $diaDiemHtml,
                'tuan'                        => $weeks,
                'trang_thai'                  => $status,
                'trang_thai_slug'             => $statusSlug,
                'ly_do_tam_hoan'              => trim((string) ($kh->ly_do_tam_hoan ?? '')),
                'registered_students_count'   => (int) ($kh->registered_students_count ?? 0),
                'admin_registration_url'      => url('/admin/dang-ky-hoc-vien?khoa_hoc_id=' . $kh->id),
                'primary_schedule_text'       => $primarySession['schedule'] ?? '',
                'primary_location_text'       => $primarySession['location'] ?? '',
            ];
        });

        $years  = KhoaHoc::query()->select('nam')->distinct()->orderBy('nam', 'desc')->pluck('nam')->all();
        if (empty($years)) {
            $years = [now()->year];
        }
        $months = range(1, 12);

        $weekOptionsMonth = $month;
        if ($weekOptionsMonth === null) {
            if ($mode === 'week' && $week) {
                $weekOptionsMonth = $this->resolveMonthFromWeek($year, $week);
            } else {
                $weekOptionsMonth = now()->month;
            }
        }

        $weeks  = $this->buildWeekOptions($year, (int) $weekOptionsMonth);

        $now = Carbon::now();
        $featuredYear   = $this->buildFeaturedStudents($now->copy()->startOfYear(), $now);
        $featuredRecent = $this->buildFeaturedStudents($now->copy()->subDays(120), $now);

        return view('home', [
            'filterMode'       => $mode,
            'year'             => $year,
            'week'             => $week,
            'month'            => $month ?? null,
            'years'            => $years,
            'months'           => $months,
            'weeks'            => $weeks,
            'rows'             => $rows,
            'featuredYear'     => $featuredYear,
            'featuredRecent'   => $featuredRecent,
        ]);
    }

    public function registeredStudents(KhoaHoc $khoaHoc): JsonResponse
    {
        $registrations = $khoaHoc->dangKies()
            ->with(['hocVien.donVi'])
            ->orderBy('id')
            ->get()
            ->map(function ($dangKy, $index) {
                $hocVien = $dangKy->hocVien;

                return [
                    'stt'      => $index + 1,
                    'ms'       => $hocVien?->msnv ?? '—',
                    'nam_sinh' => $this->formatYear($hocVien?->nam_sinh),
                    'ho_ten'   => $hocVien?->ho_ten ?? '—',
                    'chuc_vu'  => $hocVien?->chuc_vu ?? '—',
                    'don_vi'   => $this->formatDonVi($hocVien?->donVi),
                ];
            })
            ->values();

        return response()->json([
            'course' => [
                'id'   => $khoaHoc->id,
                'name' => $khoaHoc->ten_khoa_hoc,
            ],
            'registrations' => $registrations,
        ]);
    }

    public function lookupResults(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json([
                'message'     => 'Vui lòng nhập Mã số, Họ & Tên hoặc Email để tra cứu.',
                'completed'   => [],
                'incompleted' => [],
            ], 422);
        }

        $likeTerm = '%' . $term . '%';

        $completed = HocVienHoanThanh::query()
            ->with(['hocVien.donVi', 'khoaHoc', 'ketQua'])
            ->where(function ($query) use ($likeTerm) {
                $query->whereHas('hocVien', function ($sub) use ($likeTerm) {
                    $sub->where('msnv', 'like', $likeTerm)
                        ->orWhere('ho_ten', 'like', $likeTerm)
                        ->orWhere('email', 'like', $likeTerm);
                });
            })
            ->orderByDesc('ngay_hoan_thanh')
            ->limit(100)
            ->get()
            ->map(function ($record, $index) {
                $hocVien = $record->hocVien;
                $khoaHoc = $record->khoaHoc;
                $ketQua  = $record->ketQua;

                return [
                    'stt'            => $index + 1,
                    'ms'             => $hocVien?->msnv ?? '—',
                    'ho_ten'         => $hocVien?->ho_ten ?? '—',
                    'ngay_sinh'      => $this->formatDate($hocVien?->ngay_sinh),
                    'nam_sinh'       => $this->formatYear($hocVien?->nam_sinh),
                    'cong_ty'        => $hocVien?->donVi?->cong_ty_ban_nvqt ?? '—',
                    'thaco'          => $hocVien?->donVi?->thaco_tdtv ?? '—',
                    'chuc_vu'        => $hocVien?->chuc_vu ?? '—',
                    'gioi_tinh'      => $this->formatGender($hocVien?->gioi_tinh),
                    'ten_khoa_hoc'   => $khoaHoc?->ten_khoa_hoc ?? '—',
                    'ma_khoa'        => $khoaHoc?->ma_khoa_hoc ?? '—',
                    'dtb'            => $ketQua?->diem_trung_binh,
                    'gio_thuc_hoc'   => $ketQua?->tong_so_gio_thuc_te,
                    'ngay_hoan_thanh'=> $record->ngay_hoan_thanh ? Carbon::parse($record->ngay_hoan_thanh)->format('d/m/Y') : null,
                    'chung_nhan'     => $this->resolveCertificateUrl($record),
                    'chung_nhan_ten' => $this->resolveCertificateLabel($record),
                ];
            })
            ->values();

        $incompleted = HocVienKhongHoanThanh::query()
            ->with(['hocVien.donVi', 'khoaHoc', 'ketQua'])
            ->where(function ($query) use ($likeTerm) {
                $query->whereHas('hocVien', function ($sub) use ($likeTerm) {
                    $sub->where('msnv', 'like', $likeTerm)
                        ->orWhere('ho_ten', 'like', $likeTerm)
                        ->orWhere('email', 'like', $likeTerm);
                });
            })
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get()
            ->map(function ($record, $index) {
                $hocVien = $record->hocVien;
                $khoaHoc = $record->khoaHoc;

                return [
                    'stt'          => $index + 1,
                    'ms'           => $hocVien?->msnv ?? '—',
                    'ho_ten'       => $hocVien?->ho_ten ?? '—',
                    'ngay_sinh'    => $this->formatDate($hocVien?->ngay_sinh),
                    'nam_sinh'     => $this->formatYear($hocVien?->nam_sinh),
                    'cong_ty'      => $hocVien?->donVi?->cong_ty_ban_nvqt ?? '—',
                    'thaco'        => $hocVien?->donVi?->thaco_tdtv ?? '—',
                    'chuc_vu'      => $hocVien?->chuc_vu ?? '—',
                    'gioi_tinh'    => $this->formatGender($hocVien?->gioi_tinh),
                    'ten_khoa_hoc' => $khoaHoc?->ten_khoa_hoc ?? '—',
                    'ma_khoa'      => $khoaHoc?->ma_khoa_hoc ?? '—',
                    'ly_do'        => $this->formatIncompleteReason($record),
                ];
            })
            ->values();

        $profileSource = $completed->first() ?? $incompleted->first();
        $profile = null;

        if ($profileSource) {
            $profile = [
                'ms'        => $profileSource['ms'] ?? '—',
                'ho_ten'    => $profileSource['ho_ten'] ?? '—',
                'ngay_sinh' => $profileSource['ngay_sinh'] ?? null,
                'gioi_tinh' => $profileSource['gioi_tinh'] ?? null,
                'cong_ty'   => $profileSource['cong_ty'] ?? '—',
                'thaco'     => $profileSource['thaco'] ?? '—',
            ];
        }

        return response()->json([
            'completed'   => $completed,
            'incompleted' => $incompleted,
            'profile'     => $profile,
        ]);
    }

    protected function buildWeekOptions(int $year, ?int $month = null): array
    {
        $month = $month ?: now()->month;
        $month = max(1, min(12, (int) $month));

        try {
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        } catch (\Exception $exception) {
            $fallbackNow = now();
            $monthStart  = Carbon::create($fallbackNow->year, $fallbackNow->month, 1)->startOfDay();
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $weeks = [];

        $weekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        while ($weekStart->month !== $month && $weekStart->lte($monthEnd)) {
            $weekStart->addWeek();
        }

        while ($weekStart->lte($monthEnd) && $weekStart->month === $month) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $weeks[] = [
                'value' => $weekStart->isoWeek,
                'label' => sprintf(
                    '%d (%s - %s)',
                    $weekStart->isoWeek,
                    $weekStart->format('d/m'),
                    $weekEnd->format('d/m/Y')
                ),
            ];

            $weekStart->addWeek();
        }

        if (empty($weeks)) {
            $weeksInYear = Carbon::create($year, 12, 28)->isoWeek();

            return collect(range(1, $weeksInYear))->map(function ($week) use ($year) {
                $start = Carbon::now()->setISODate($year, $week, 1)->startOfDay();
                $end   = (clone $start)->addDays(6);

                return [
                    'value' => $week,
                    'label' => sprintf('%d (%s - %s)', $week, $start->format('d/m'), $end->format('d/m/Y')),
                ];
            })->all();
        }

        return array_values($weeks);
    }

    protected function resolveMonthFromWeek(int $year, int $week): int
    {
        $week = max(1, min(53, $week));

        try {
            return (int) Carbon::now()->setISODate($year, $week, 1)->month;
        } catch (\Exception $exception) {
            return now()->month;
        }
    }

    protected function formatDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y');
        }

        return rescue(fn () => Carbon::parse($value)->format('d/m/Y'), null, false);
    }

    protected function buildFeaturedStudents(Carbon $from, Carbon $to, int $limit = 3): array
    {
        $stats = HocVienHoanThanh::query()
            ->select([
                'hoc_vien_hoan_thanhs.hoc_vien_id as hoc_vien_id',
                DB::raw('COUNT(DISTINCT hoc_vien_hoan_thanhs.ket_qua_khoa_hoc_id) as total_courses'),
                DB::raw('MAX(COALESCE(ket_qua_khoa_hocs.diem_trung_binh, 0)) as best_score'),
                DB::raw('SUM(COALESCE(ket_qua_khoa_hocs.tong_so_gio_thuc_te, 0)) as total_hours'),
            ])
            ->whereNotNull('hoc_vien_hoan_thanhs.hoc_vien_id')
            ->when($from, fn ($q) => $q->whereDate('hoc_vien_hoan_thanhs.ngay_hoan_thanh', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->whereDate('hoc_vien_hoan_thanhs.ngay_hoan_thanh', '<=', $to->toDateString()))
            ->leftJoin('ket_qua_khoa_hocs', 'ket_qua_khoa_hocs.id', '=', 'hoc_vien_hoan_thanhs.ket_qua_khoa_hoc_id')
            ->groupBy('hoc_vien_hoan_thanhs.hoc_vien_id')
            ->orderByDesc('total_courses')
            ->orderByDesc('best_score')
            ->orderByDesc('total_hours')
            ->limit($limit)
            ->get();

        if ($stats->isEmpty()) {
            return [];
        }

        $hocVienIds = $stats->pluck('hoc_vien_id')->all();
        $hocViens = HocVien::query()
            ->with('donVi')
            ->whereIn('id', $hocVienIds)
            ->get()
            ->keyBy('id');

        return $stats->map(function ($stat) use ($hocViens) {
            $hocVien = $hocViens->get($stat->hoc_vien_id);
            if (! $hocVien) {
                return null;
            }

            $courses = (int) $stat->total_courses;
            $score   = $stat->best_score !== null ? (float) $stat->best_score : null;
            $hours   = $stat->total_hours !== null ? (float) $stat->total_hours : null;

            return [
                'ms'          => $hocVien->msnv ?? '—',
                'name'        => $hocVien->ho_ten ?? '—',
                'position'    => $hocVien->chuc_vu ?? '—',
                'company'     => $hocVien->donVi?->cong_ty_ban_nvqt ?? '—',
                'group'       => $hocVien->donVi?->thaco_tdtv ?? '—',
                'avatar'      => $this->resolveAvatarUrl($hocVien->hinh_anh_path),
                'achievement' => $this->formatAchievementSummary($courses, $score, $hours),
            ];
        })->filter()->values()->all();
    }

    protected function formatYear($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y');
        }

        if (is_numeric($value) && strlen((string) $value) === 4) {
            return (string) $value;
        }

        return rescue(fn () => Carbon::parse($value)->format('Y'), function () use ($value) {
            return substr((string) $value, 0, 4) ?: null;
        }, false);
    }

    protected function formatGender($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        $normalized = Str::lower($string);

        if (in_array($normalized, ['1', 'nam', 'male', 'm'], true)) {
            return 'Nam';
        }

        if (in_array($normalized, ['0', 'nu', 'nữ', 'female', 'f'], true)) {
            return 'Nữ';
        }

        return ucfirst($string);
    }

    protected function formatDonVi($donVi): string
    {
        if (! $donVi) {
            return '—';
        }

        $parts = array_filter([
            $donVi->phong_bo_phan ?? null,
            $donVi->cong_ty_ban_nvqt ?? null,
            $donVi->thaco_tdtv ?? null,
        ]);

        if (! empty($parts)) {
            return implode(', ', array_map(fn ($part) => trim((string) $part), $parts));
        }

        $fallback = $donVi->ten_hien_thi ?? '';
        if ($fallback === '') {
            return '—';
        }

        $normalized = str_replace('•', ',', $fallback);
        $normalized = preg_replace('/\s*,\s*/u', ', ', $normalized) ?? $normalized;

        return trim($normalized) !== '' ? trim($normalized) : '—';
    }

    protected function summarizePrimarySession($lichFiltered): array
    {
        $first = $lichFiltered instanceof \Illuminate\Support\Collection
            ? $lichFiltered->first()
            : (is_array($lichFiltered) ? reset($lichFiltered) : null);

        if (! $first) {
            return ['schedule' => null, 'location' => null];
        }

        $date       = null;
        $dateString = null;
        $weekday    = null;
        if (! empty($first->ngay_hoc)) {
            $date = rescue(function () use ($first) {
                return Carbon::parse($first->ngay_hoc);
            }, null, false);

            if ($date instanceof Carbon) {
                $dateString = $date->format('d/m/Y');
                $weekday    = $this->formatWeekday($date);
            }
        }

        $startTime = null;
        if (! empty($first->gio_bat_dau)) {
            $startTime = substr($first->gio_bat_dau, 0, 5);
        }

        $endTime = null;
        if (! empty($first->gio_ket_thuc)) {
            $endTime = substr($first->gio_ket_thuc, 0, 5);
        }

        $time = null;
        if ($startTime && $endTime) {
            $time = $startTime . ' - ' . $endTime;
        } elseif ($startTime) {
            $time = $startTime;
        } elseif ($endTime) {
            $time = $endTime;
        }

        $schedule = null;
        $segments = [];

        if ($time) {
            $segments[] = 'Thời gian: ' . $time;
        }

        if ($dateString) {
            $datePart = 'ngày ' . $dateString;
            if ($weekday) {
                $datePart .= ' (' . $weekday . ')';
            }
            $segments[] = $datePart;
        }

        if (empty($segments) && $dateString) {
            $segments[] = 'Ngày ' . $dateString . ($weekday ? ' (' . $weekday . ')' : '');
        }

        if (! empty($segments)) {
            $schedule = implode(', ', $segments);
        }

        return [
            'schedule' => $schedule,
            'location' => $this->formatLocation($first->diaDiem ?? null),
        ];
    }

    protected function formatWeekday(Carbon $date): ?string
    {
        $names = [
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
            7 => 'Chủ nhật',
        ];

        $key = $date->isoWeekday();

        return $names[$key] ?? null;
    }

    protected function formatLocation($diaDiem): ?string
    {
        if (! $diaDiem) {
            return null;
        }

        foreach (['ten_phong', 'ma_phong'] as $field) {
            $value = trim((string) ($diaDiem->{$field} ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveCertificateUrl(HocVienHoanThanh $record): ?string
    {
        $link = trim((string) ($record->chung_chi_link ?? ''));
        if ($link !== '') {
            return $this->ensureUrl($link);
        }

        $file = trim((string) ($record->chung_chi_tap_tin ?? ''));
        if ($file !== '') {
            return $this->resolveStorageUrl($file);
        }

        return null;
    }

    protected function resolveCertificateLabel(HocVienHoanThanh $record): ?string
    {
        $file = trim((string) ($record->chung_chi_tap_tin ?? ''));
        if ($file !== '') {
            $basename = basename($file);
            return $basename !== '' ? $basename : null;
        }

        $link = trim((string) ($record->chung_chi_link ?? ''));
        if ($link === '') {
            return null;
        }

        $path = parse_url($link, PHP_URL_PATH) ?: '';
        $decoded = urldecode($path);
        $basename = basename($decoded);

        return $basename !== '' ? $basename : null;
    }

    protected function resolveAvatarUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return $this->resolveStorageUrl($path);
    }

    protected function resolveStorageUrl(string $path): string
    {
        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        if (Str::startsWith($normalized, 'storage/')) {
            return url('/' . $normalized);
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        return url('/storage/' . ltrim($normalized, '/'));
    }

    protected function ensureUrl(string $value): string
    {
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return url($value);
    }

    protected function formatIncompleteReason(HocVienKhongHoanThanh $record): string
    {
        $raw = trim((string) ($record->ly_do_khong_hoan_thanh ?? ''));

        if ($raw === '') {
            return '—';
        }

        $lower = Str::lower($raw);

        if (Str::contains($lower, ['giờ', 'gio', 'hour'])) {
            return 'Không đủ số giờ giảng';
        }

        if (Str::contains($lower, ['đtb', 'diem', 'điểm', 'grade'])) {
            return 'ĐTB không đạt';
        }

        if (Str::contains($lower, ['vắng', 'vang'])) {
            return 'Vắng: ' . $raw;
        }

        return $raw;
    }

    protected function formatAchievementSummary(int $courses, ?float $score, ?float $hours): string
    {
        $parts = [];
        $parts[] = 'Tổng khóa học: ' . $courses;

        if ($score !== null) {
            $parts[] = 'ĐTB cao nhất: ' . number_format($score, 1);
        }

        if ($hours !== null) {
            $parts[] = 'Tổng giờ học: ' . number_format($hours, 1) . ' giờ';
        }

        return implode(' • ', $parts);
    }
}
