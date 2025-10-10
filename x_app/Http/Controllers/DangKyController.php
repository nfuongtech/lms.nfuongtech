<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HocVien;
use App\Models\DangKy;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ChuyenDe;
use App\Models\DonVi;
use Illuminate\Support\Facades\DB;

class DangKyController extends Controller
{
    public function index(Request $request)
    {
        // filterType: 'week' or 'month'
        $filterType = $request->get('filterType', 'week');
        $year = $request->get('year', date('Y'));
        $week = $request->get('week', Carbon::now()->weekOfYear);
        $month = $request->get('month', Carbon::now()->month);

        // Build LichHoc filter scope
        $lichQuery = LichHoc::query();

        if ($filterType === 'week') {
            $lichQuery->where('tuan', $week)->where('nam', $year);
        } else { // month
            $lichQuery->where('thang', $month)->where('nam', $year);
        }

        // Block 1: chuyen_de list + count of hoc vien registered (filtered by week/month)
        // We join lich_hocs -> khoa_hocs -> dang_kies
        $chuyenDes = ChuyenDe::select('chuyen_des.*')
            ->withCount(['lichHocs as hoc_vien_count' => function($q) use ($lichQuery) {
                // Count unique hoc_vien through dang_kies joined to khoa_hocs -> lich_hocs limited by filter
                $q->select(DB::raw('COUNT(DISTINCT dang_kies.hoc_vien_id)'))
                  ->join('khoa_hocs', 'lich_hocs.khoa_hoc_id', '=', 'khoa_hocs.id')
                  ->join('dang_kies', 'dang_kies.khoa_hoc_id', '=', 'khoa_hocs.id');
                // apply the same filter conditions on lich_hocs (date/week/month)
            }])->get();

        // Block 2: danh sách dang_kies (registrations) filtered by week/month
        $dangKyQuery = DangKy::query()
            ->select('dang_kies.*')
            ->join('khoa_hocs', 'dang_kies.khoa_hoc_id', '=', 'khoa_hocs.id')
            ->join('lich_hocs', 'lich_hocs.khoa_hoc_id', '=', 'khoa_hocs.id')
            ->join('hoc_viens', 'hoc_viens.id', '=', 'dang_kies.hoc_vien_id');

        if ($filterType === 'week') {
            $dangKyQuery->where('lich_hocs.tuan', $week)->where('lich_hocs.nam', $year);
        } else {
            $dangKyQuery->where('lich_hocs.thang', $month)->where('lich_hocs.nam', $year);
        }

        $dangKies = $dangKyQuery->with(['hocVien', 'khoaHoc'])->distinct('dang_kies.id')->paginate(30);

        // For the paste helper (list of active hv)
        $activeHocViens = HocVien::where('tinh_trang','Đang làm việc')->limit(200)->get();

        return view('dang-kies.index', compact('chuyenDes', 'dangKies', 'activeHocViens', 'filterType', 'week', 'month', 'year'));
    }

    /**
     * Lookup hoc vien by pasted MSNV string: "HV01,HV02,HV03"
     * Returns JSON array of found students with display label.
     */
    public function lookupHocViens(Request $request)
    {
        $msnvString = $request->input('msnv', '');
        $arr = array_filter(array_map('trim', explode(',', $msnvString)));

        $hocviens = HocVien::whereIn('msnv', $arr)
            ->where('tinh_trang', 'Đang làm việc')
            ->with('donVi') // if relation exists
            ->get()
            ->map(function($hv){
                $donviName = optional($hv->donVi)->thaco_tdtv ?? '';
                return [
                    'id' => $hv->id,
                    'msnv' => $hv->msnv,
                    'label' => "{$hv->msnv} - {$hv->ho_ten}" . ($donviName ? ", {$donviName}" : "")
                ];
            });

        return response()->json(['data' => $hocviens]);
    }

    /**
     * Store multiple DangKy (bulk) with conflict checks.
     * Input:
     *  - khoa_hoc_id
     *  - msnv_list (array of msnv)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hocs,id',
            'msnv_list' => 'required|array',
            'msnv_list.*' => 'required|string',
        ]);

        $khoaHoc = KhoaHoc::findOrFail($data['khoa_hoc_id']);
        $msnvList = $data['msnv_list'];

        // get the sessions for this khoa_hoc
        $thisLichHocs = LichHoc::where('khoa_hoc_id', $khoaHoc->id)->get();

        $results = [
            'created' => [],
            'skipped' => [] // contain reason
        ];

        DB::beginTransaction();
        try {
            foreach ($msnvList as $msnv) {
                $msnv = trim($msnv);
                $hocVien = HocVien::where('msnv', $msnv)->where('tinh_trang','Đang làm việc')->first();

                if (!$hocVien) {
                    $results['skipped'][] = [
                        'msnv' => $msnv,
                        'reason' => 'Không tìm thấy hoặc không "Đang làm việc"'
                    ];
                    continue;
                }

                // if already registered to this same khoa_hoc -> skip
                $existsSame = DangKy::where('khoa_hoc_id', $khoaHoc->id)->where('hoc_vien_id', $hocVien->id)->exists();
                if ($existsSame) {
                    $results['skipped'][] = ['msnv' => $msnv, 'reason' => 'Đã ghi danh vào khóa học này'];
                    continue;
                }

                // Conflict detection: for each lich_hoc of the target khoa_hoc, check other lich_hocs of other khoa_hocs
                $conflictFound = false;
                $conflictDetails = null;

                foreach ($thisLichHocs as $targetLich) {
                    // find other lich_hocs (different khoa_hoc) where that hoc_vien is registered (dang_kies) and chuyen_de_id same and ngay_hoc same and time overlap
                    $conflicting = LichHoc::query()
                        ->join('khoa_hocs','lich_hocs.khoa_hoc_id','=','khoa_hocs.id')
                        ->join('dang_kies','dang_kies.khoa_hoc_id','=','khoa_hocs.id')
                        ->where('dang_kies.hoc_vien_id', $hocVien->id)
                        ->where('lich_hocs.chuyen_de_id', $targetLich->chuyen_de_id)
                        ->where('lich_hocs.ngay_hoc', $targetLich->ngay_hoc)
                        ->where(function($q) use ($targetLich) {
                            // overlap: new_start < exist_end AND new_end > exist_start
                            $q->whereRaw('? < time_to_sec(lich_hocs.gio_ket_thuc)', [$targetLich->gio_bat_dau])
                              ->whereRaw('? > time_to_sec(lich_hocs.gio_bat_dau)', [$targetLich->gio_ket_thuc]);
                        })
                        ->select('lich_hocs.*','khoa_hocs.ma_khoa_hoc','dang_kies.id as dang_ky_id')
                        ->first();

                    if ($conflicting) {
                        $conflictFound = true;
                        $conflictDetails = [
                            'target_lich' => $targetLich->only(['id','ngay_hoc','gio_bat_dau','gio_ket_thuc','chuyen_de_id']),
                            'conflict_lich' => $conflicting->only(['id','ngay_hoc','gio_bat_dau','gio_ket_thuc']),
                            'conflict_khoa' => $conflicting->ma_khoa_hoc,
                        ];
                        break;
                    }
                }

                if ($conflictFound) {
                    $results['skipped'][] = [
                        'msnv' => $msnv,
                        'reason' => 'Xung đột lịch học',
                        'detail' => $conflictDetails
                    ];
                    continue;
                }

                // create DangKy
                DangKy::create([
                    'hoc_vien_id' => $hocVien->id,
                    'khoa_hoc_id' => $khoaHoc->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $results['created'][] = ['msnv' => $msnv, 'hoc_vien_id' => $hocVien->id];
            }

            DB::commit();
            return response()->json(['status' => 'ok', 'results' => $results]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>'error','message'=>$e->getMessage()], 500);
        }
    }
}
