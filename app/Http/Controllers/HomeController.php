<?php

namespace App\Http\Controllers;

use App\Models\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Tham số filter (riêng lẻ) theo thứ tự ưu tiên: Tuần -> Tháng -> Năm
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

        // Query theo mode (luôn gắn theo năm đang xét)
        $records = KhoaHoc::query()
            ->where('nam', $year)
            ->when($mode === 'week',  fn ($q) => $q->whereHas('lichHocs', fn ($h) => $h->where('tuan',  $week)))
            ->when($mode === 'month', fn ($q) => $q->whereHas('lichHocs', fn ($h) => $h->where('thang', $month)))
            ->with([
                'lichHocs.giangVien' => fn ($q) => $q->select(['id','ho_ten']),
                'lichHocs.diaDiem'   => fn ($q) => $q->select(['id','ten_phong','ma_phong']),
            ])
            ->get();

        // Chuẩn bị + sắp xếp theo phiên mới nhất (giảm dần)
        $prepared = $records->map(function ($kh) use ($mode, $week, $month) {
            $lichFiltered = $kh->lichHocs
                ->filter(function ($lh) use ($mode, $week, $month) {
                    if ($mode === 'week')  return (int) $lh->tuan  === (int) $week;
                    if ($mode === 'month') return (int) $lh->thang === (int) $month;
                    return true; // year
                });

            $latest = $lichFiltered->map(function ($lh) {
                $day = $lh->ngay_hoc instanceof \DateTimeInterface
                    ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                    : Carbon::parse($lh->ngay_hoc)->startOfDay();

                $end   = $lh->gio_ket_thuc ? (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc) : null;
                $start = $lh->gio_bat_dau  ? (clone $day)->setTimeFromTimeString($lh->gio_bat_dau)  : null;
                return $end ?? $start ?? $day;
            })->filter();

            $latestDt = $latest->count() ? $latest->max() : Carbon::create(1970,1,1,0,0,0);

            return [
                'record'       => $kh,
                'lichFiltered' => $lichFiltered
                    ->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']]) // hiển thị tăng dần trong cell
                    ->values(),
                'latest'       => $latestDt,
            ];
        })->sortByDesc('latest')->values();

        $today = Carbon::today();

        // Dựng rows hiển thị (kèm HTML highlight cho ngày hôm nay)
        $rows = $prepared->map(function ($p) use ($mode, $week, $today) {
            /** @var \App\Models\KhoaHoc $kh */
            $kh = $p['record'];
            $lichFiltered = $p['lichFiltered'];

            // Giảng viên (duy nhất)
            $giangViens = $lichFiltered
                ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $giangVienText = !empty($giangViens) ? implode(', ', $giangViens) : '—';

            // Ngày, Giờ (mỗi lịch 1 dòng; nếu là hôm nay -> bôi xanh + in đậm)
            $ngayGioLines = $lichFiltered->map(function ($lh) use ($today) {
                $date  = Carbon::parse($lh->ngay_hoc);
                $dateStr = $date->format('d/m/Y');
                $start = $lh->gio_bat_dau  ? substr($lh->gio_bat_dau, 0, 5)  : '';
                $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                $line  = "{$dateStr}, {$start}-{$end}";
                if ($date->isSameDay($today)) {
                    return '<span class="session today">'.$line.'</span>';
                }
                return '<span class="session">'.$line.'</span>';
            })->all();
            $ngayGioHtml = $ngayGioLines ? implode('<br>', $ngayGioLines) : '—';

            // Địa điểm tương ứng từng dòng
            $diaDiemLines = $lichFiltered->map(function ($lh) {
                $dd = $lh->diaDiem;
                return $dd?->ten_phong ?? $dd?->ma_phong ?? '';
            })->all();
            $diaDiemHtml = $diaDiemLines ? implode('<br>', $diaDiemLines) : '—';

            // Tuần (theo dữ liệu sau lọc)
            $weeks = $lichFiltered->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ');
            if ($weeks === '') $weeks = $mode === 'week' ? (string) $week : '—';

            // Trạng thái (giữ logic cũ)
            $qs = $kh->lichHocs()->select('ngay_hoc','gio_bat_dau','gio_ket_thuc');
            if (!$qs->exists()) {
                $status = 'Dự thảo';
            } else {
                $all = $qs->get()->map(function ($lh) {
                    $day = $lh->ngay_hoc instanceof \DateTimeInterface
                        ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                        : Carbon::parse($lh->ngay_hoc)->startOfDay();
                    $start = (clone $day)->setTimeFromTimeString($lh->gio_bat_dau ?: '00:00:00');
                    $end   = (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc ?: '23:59:59');
                    return compact('start','end');
                });
                $minStart = $all->min('start');
                $maxEnd   = $all->max('end');
                $now = now();

                if ($now->lt($minStart))      $status = 'Ban hành';
                elseif ($now->between($minStart, $maxEnd)) $status = 'Đang đào tạo';
                else                           $status = 'Kết thúc';
            }

            return [
                'ma_khoa_hoc'  => $kh->ma_khoa_hoc,
                'ten_khoa_hoc' => $kh->ten_khoa_hoc,
                'giang_vien'   => $giangVienText,
                'ngay_gio_html'=> $ngayGioHtml,
                'dia_diem_html'=> $diaDiemHtml,
                'tuan'         => $weeks,
                'trang_thai'   => $status,
            ];
        });

        // Dropdown
        $years  = KhoaHoc::query()->select('nam')->distinct()->orderBy('nam','desc')->pluck('nam')->all();
        if (empty($years)) $years = [now()->year];
        $months = range(1, 12);
        $weeks  = range(1, 53);

        return view('home', [
            'filterMode' => $mode,
            'year'   => $year,
            'week'   => $week,
            'month'  => $month ?? null,
            'years'  => $years,
            'months' => $months,
            'weeks'  => $weeks,
            'rows'   => $rows,
        ]);
    }
}
