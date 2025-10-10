<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ThongKeHocVienExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data->toArray();
    }

    public function headings(): array
    {
        return [
            'STT',
            'THACO/TĐTV',
            'Công ty/Ban NVQT',
            'Tổng số HV',
            'Số lượng HV (Đang làm việc)',
        ];
    }
}
