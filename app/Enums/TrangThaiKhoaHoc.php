<?php

namespace App\Enums;

enum TrangThaiKhoaHoc: string
{
    case KE_HOACH = 'ke_hoach';
    case BAN_HANH = 'ban_hanh';
    case CHINH_SUA_KE_HOACH = 'chinh_sua_ke_hoach';
    case DANG_DAO_TAO = 'dang_dao_tao';
    case TAM_HOAN = 'tam_hoan';
    case KET_THUC = 'ket_thuc';

    public function label(): string
    {
        return match ($this) {
            self::KE_HOACH => 'Kế hoạch',
            self::BAN_HANH => 'Ban hành',
            self::CHINH_SUA_KE_HOACH => 'Chỉnh sửa kế hoạch',
            self::DANG_DAO_TAO => 'Đang đào tạo',
            self::TAM_HOAN => 'Tạm hoãn',
            self::KET_THUC => 'Kết thúc',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::KE_HOACH => 'gray',
            self::BAN_HANH => 'success',
            self::CHINH_SUA_KE_HOACH => 'warning',
            self::DANG_DAO_TAO => 'primary',
            self::TAM_HOAN => 'danger',
            self::KET_THUC => 'secondary',
        };
    }

    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $c) {
            $out[$c->value] = $c->label();
        }
        return $out;
    }

    public static function colorsMap(): array
    {
        $map = [];
        foreach (self::cases() as $c) {
            $map[$c->value] = $c->color();
        }
        return $map;
    }
}
