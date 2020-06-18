<?php

namespace App\Http\Controllers;

use App\Exports\KursBkfExport;
use App\Exports\KursExport;

class ExcelController extends Controller
{
    // export kurs data
    public function exportKurs($tanggal = null) {
        $filename = "Kurs";
        if ($tanggal) {
            $filename .= " Per " . $tanggal;
        }

        $filename .= '.xlsx';
        return (new KursExport)->perTanggal($tanggal)->download($filename);
    }

    // export kurs direct from bkf
    public function exportKursBkf() {
        return (new KursBkfExport)->download('KursBKF Per Tanggal ' . date('d-m-Y') . '.xlsx');
    }
}
