<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleArrayExport implements FromArray, WithHeadings
{
    /**
     * @var array<int, array<int, mixed>>
     */
    protected array $rows;

    /**
     * @var array<int, string>
     */
    protected array $headings;

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $headings
     */
    public function __construct(array $rows, array $headings)
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
