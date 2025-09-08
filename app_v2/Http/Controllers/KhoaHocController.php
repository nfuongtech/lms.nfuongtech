<?php

namespace App\Http\Controllers;

use App\Models\KhoaHoc;
use Illuminate\Http\Request;

class KhoaHocController extends Controller
{
    public function index()
    {
        return response()->json(KhoaHoc::with('lichHocs')->get());
    }

    public function show($id)
    {
        return response()->json(KhoaHoc::with('lichHocs')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_khoa_hoc' => 'required|string|max:255',
            'chuong_trinh_id' => 'nullable|exists:chuong_trinhs,id',
            'giang_vien_id' => 'nullable|exists:giang_viens,id',
            'trang_thai' => 'required|string|max:50',
        ]);

        $khoaHoc = KhoaHoc::create($data);
        return response()->json($khoaHoc, 201);
    }

    public function update(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $data = $request->validate([
            'ten_khoa_hoc' => 'sometimes|string|max:255',
            'chuong_trinh_id' => 'nullable|exists:chuong_trinhs,id',
            'giang_vien_id' => 'nullable|exists:giang_viens,id',
            'trang_thai' => 'sometimes|string|max:50',
        ]);

        $khoaHoc->update($data);
        return response()->json($khoaHoc);
    }

    public function destroy($id)
    {
        KhoaHoc::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa khóa học']);
    }
}
