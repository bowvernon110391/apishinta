<?php

namespace App\Http\Controllers;

use App\Exports\KursBkfExport;
use App\Exports\KursExport;
use App\Imports\KursImportToJson;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends ApiController
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

    // import kurs excel?
    public function importKurs(Request $r) {
        try {
            $data = Excel::toArray(new KursImportToJson, $r->file('file') );

            // grab only the first worksheet
            return $this->respondWithArray([
                'data' => $data[0]
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
