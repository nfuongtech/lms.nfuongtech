<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SimpleArrayExport implements FromArray
{
    /**
     * @var array<int, array<int, mixed>>
     */
    protected array $rows;

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $headings
     */
    public function __construct(array $rows, array $headings = [])
    {
        if (!empty($headings)) {
            array_unshift($rows, $headings);
        }

        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
