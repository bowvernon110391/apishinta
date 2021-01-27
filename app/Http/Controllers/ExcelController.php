<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\Exports\BPPMExport;
use App\Exports\KursBkfExport;
use App\Exports\KursExport;
use App\Imports\BillingImport;
use App\Imports\KursImportToJson;
use App\Imports\PIBKImport;
use App\Transformers\PIBKTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // export bppm
    public function exportBppm(Request $r) {
        try {
            $filename = 'bppm.xlsx';

            return (new BPPMExport)->buildQuery($r)->download($filename);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // import billing
    public function importBilling(Request $r) {
        DB::beginTransaction();
        try {
            $file = $r->file('file');

            if (!$file) throw new \Exception("No excel file provided!");

            $data = (new BillingImport)->importToModels($file);

            // just directly insert into db
            $total = count($data);
            $inserted = 0;
            $omitted = 0;

            foreach ($data as $b) {
                // check if payable already has billing
                if ($b->billable->billing()->count()) {
                    $omitted++;
                    continue;
                }

                // it doesn't, go on
                $b->save();

                // and log who's inserting it
                AppLog::logInfo("Billing #{$b->id} nomor {$b->nomor} untuk dokumen {$b->billable->jenis_dokumen} #{$b->billable->id} diinput oleh {$r->userInfo['username']}", $b);
                AppLog::logInfo("Dokumen {$b->billable->jenis_dokumen} #{$b->billable->id} nomor {$b->billable->nomor_lengkap} diinput data billingnya dengan billing #{$b->id} nomor {$b->nomor} oleh {$r->userInfo['username']}", $b->billable);

                $inserted++;
            }

            DB::commit();

            return $this->respondWithArray([
                'total' => (int) $total,
                'inserted' => (int) $inserted,
                'omitted' => (int) $omitted
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function importPIBK(Request $r) {

        // this is bad. might want to move it to a dedicated middleware
        $ip = $r->getClientIp();
        $ipAllowed = false;

        // allowed: local lan (192.168.*), localhost (127.0.0.*), kantor (125.213.132.*)
        if (preg_match('/^192.168./', $ip) || preg_match('/^127.0.0./', $ip) || preg_match('/^125.213.132./', $ip)) {
            $ipAllowed = true;
        }

        try {
            // is ip allowed?
            if (!$ipAllowed) {
                throw new \Exception("Akses dari ip: {$ip} tidak diperbolehkan!");
            }

            $file = $r->file('file');

            if (!$file) {
                throw new \Exception("No excel file provided!");
            }

            // baca file?
            $p = new PIBKImport();
            Excel::import($p, $file);

            $pibk = $p->getPIBK();

            if (!$pibk) {
                throw new \Exception("PIBK null! This shouldn't happen!!!!");
            }

            return $this->respondWithItem($pibk, new PIBKTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
