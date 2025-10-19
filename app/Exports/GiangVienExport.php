<?php

namespace App\Exports;

use App\Models\GiangVien;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GiangVienExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return GiangVien::with(['donVi', 'donViPhapNhan', 'user'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã số', 'Họ và tên', 'Giới tính', 'Năm sinh', 'Email', 'Số điện thoại',
            'Ngày vào', 'Chức vụ', 'Mã đơn vị', 'Tên đơn vị', 'THACO/TĐTV',
            'Công ty/Ban NVQT', 'Phòng/Bộ phận', 'Nơi làm việc chi tiết', 'Tình trạng',
            'Trình độ', 'Chuyên môn', 'Số năm kinh nghiệm', 'Tóm tắt kinh nghiệm', 'Hộ khẩu/Nơi làm việc',
            // --- THÊM: Tiêu đề cột Đơn vị pháp nhân ---
            'Mã đơn vị pháp nhân', 'Tên đơn vị pháp nhân', 'Địa chỉ', 'Ghi chú'
            // --- HẾT THÊM: Tiêu đề cột Đơn vị pháp nhân ---
        ];
    }

    /**
     * @param GiangVien $giangVien
     * @return array
     */
    public function map($giangVien): array
    {
        return [
            $giangVien->ma_so,
            $giangVien->ho_ten,
            $giangVien->gioi_tinh,
            $giangVien->nam_sinh ? date('d/m/Y', strtotime($giangVien->nam_sinh)) : 'N/A',
            $giangVien->user->email ?? 'N/A',
            $giangVien->dien_thoai ?? 'N/A',
            $giangVien->ngay_vao ? date('d/m/Y', strtotime($giangVien->ngay_vao)) : 'N/A',
            $giangVien->chuc_vu,
            $giangVien->donVi->ma_don_vi ?? 'N/A',
            $giangVien->donVi->ten_hien_thi ?? 'N/A',
            $giangVien->donVi->thaco_tdtv ?? 'N/A',
            $giangVien->donVi->cong_ty_ban_nvqt ?? 'N/A',
            $giangVien->donVi->phong_bo_phan ?? 'N/A',
            $giangVien->donVi->noi_lam_viec_chi_tiet ?? 'N/A',
            $giangVien->tinh_trang,
            $giangVien->trinh_do ?? 'N/A',
            $giangVien->chuyen_mon ?? 'N/A',
            $giangVien->so_nam_kinh_nghiem ?? 'N/A',
            $giangVien->tom_tat_kinh_nghiem ?? 'N/A',
            $giangVien->ho_khau_noi_lam_viec ?? 'N/A',
            // --- THÊM: Dữ liệu cột Đơn vị pháp nhân ---
            $giangVien->donViPhapNhan->ma_so_thue ?? 'N/A',
            $giangVien->donViPhapNhan->ten_don_vi ?? 'N/A',
            $giangVien->donViPhapNhan->dia_chi ?? 'N/A',
            $giangVien->donViPhapNhan->ghi_chu ?? 'N/A'
            // --- HẾT THÊM: Dữ liệu cột Đơn vị pháp nhân ---
        ];
    }
}
