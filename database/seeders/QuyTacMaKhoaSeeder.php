<?php

namespace Database\Seeders;

use App\Models\QuyTacMaKhoa;
use Illuminate\Database\Seeder;

class QuyTacMaKhoaSeeder extends Seeder
{
    public function run(): void
    {
        $quyTac = [
            [
                'loai_hinh_dao_tao' => 'Kỹ năng mềm',
                'tien_to' => 'KNM',
                'dinh_dang' => 'YYMMSSS',
                'mau_so' => 0
            ],
            [
                'loai_hinh_dao_tao' => 'Đào tạo thường xuyên',
                'tien_to' => 'ĐTX',
                'dinh_dang' => 'YYMMSSS',
                'mau_so' => 0
            ],
            [
                'loai_hinh_dao_tao' => 'Kỹ năng sống',
                'tien_to' => 'KNS',
                'dinh_dang' => 'YYMMSSS',
                'mau_so' => 0
            ],
            [
                'loai_hinh_dao_tao' => 'Chuyên đề nghiệp vụ',
                'tien_to' => 'NV',
                'dinh_dang' => 'YYMMSSS',
                'mau_so' => 0
            ],
        ];

        foreach ($quyTac as $item) {
            QuyTacMaKhoa::create($item);
        }
    }
}
