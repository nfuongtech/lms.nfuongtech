<?php

namespace App\Http\Controllers;

use App\Exports\ChuyenDeExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ChuyenDeExportController extends Controller
{
    public function export()
    {
        $fileName = 'chuyende_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ChuyenDeExport, $fileName);
    }
}
