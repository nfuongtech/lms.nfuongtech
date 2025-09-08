<?php

namespace App\Enums;

enum TrangThaiKeHoach: string
{
    // Trạng thái do người vận hành đặt
    case KE_HOACH = 'ke_hoach';              // Lập ban đầu
    case BAN_HANH = 'ban_hanh';              // Đã ban hành
    case THAY_DOI = 'thay_doi';              // Có thay đổi
    case TAM_HOAN = 'tam_hoan';              // Tạm hoãn

    // Trạng thái thời gian (tự suy luận theo khung giờ)
    case CHO_THUC_HIEN = 'cho_thuc_hien';    // Trước giờ bắt đầu
    case DANG_DAO_TAO = 'dang_dao_tao';      // Trong khung giờ
    case KET_THUC = 'ket_thuc';              // Quá thời gian kết thúc

    public static function labels(): array
    {
        return [
            self::KE_HOACH->value      => 'Kế hoạch',
            self::BAN_HANH->value      => 'Ban hành',
            self::THAY_DOI->value      => 'Thay đổi',
            self::TAM_HOAN->value      => 'Tạm hoãn',
            self::CHO_THUC_HIEN->value => 'Chờ thực hiện',
            self::DANG_DAO_TAO->value  => 'Đang đào tạo',
            self::KET_THUC->value      => 'Kết thúc đào tạo',
        ];
    }
}
