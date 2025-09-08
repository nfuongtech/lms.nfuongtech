<?php

namespace App\Http\Controllers;

use App\Models\LichHoc;
use Illuminate\Http\Request;

class LichHocController extends Controller
{
    public function index()
    {
        return response()->json(LichHoc::with('khoaHoc')->get());
    }

    public function show($id)
    {
        return response()->json(LichHoc::with('khoaHoc')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hocs,id',
            'ngay_hoc' => 'required|date',
            'phong_hoc' => 'required|string|max:255',
            'ghi_chu' => 'nullable|string',
        ]);

        $lichHoc = LichHoc::create($data);
        return response()->json($lichHoc, 201);
    }

    public function update(Request $request, $id)
    {
        $lichHoc = LichHoc::findOrFail($id);
        $data = $request->validate([
            'ngay_hoc' => 'sometimes|date',
            'phong_hoc' => 'sometimes|string|max:255',
            'ghi_chu' => 'nullable|string',
        ]);

        $lichHoc->update($data);
        return response()->json($lichHoc);
    }

    public function destroy($id)
    {
        LichHoc::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa lịch học']);
    }
}
