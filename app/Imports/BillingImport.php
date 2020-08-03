<?php

namespace App\Imports;

use App\Billing;
use App\BPPM;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BillingImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $models = [];

    protected $rowId = 1;

    public function model(array $r)
    {
        // increment row number (for error reporting)
        ++$this->rowId;

        // read the rest of data
        $no_bppm = trim($r['nomor_bppm']);

        // if even our first data is invalid, 
        // that means the rest is invalid too
        if (strlen($no_bppm) < 1) {
            return;
        }

        // grab bppm instance
        $bppm = BPPM::where('nomor_lengkap_dok', $no_bppm)->first();

        // if we can't find it, throw some error
        if (!$bppm) {
            throw new \Exception("error pada baris ke- {$this->rowId} ---> BPPM '{$no_bppm}' is invalid!");
            return;
        }

        // safely read billing data
        $kode_billing = $r['kode_billing'];

        $billingPattern = '/^62\d{13}$/si';
        if (!preg_match($billingPattern, $kode_billing)) {
            throw new \Exception("error pada baris ke- {$this->rowId} ---> Kode billing tidak sesuai format! => '{$kode_billing}'");
            return ;
        }

        $ntb = $r['ntb'];
        if (strlen(trim($ntb)) < 5) {
            throw new \Exception("error pada baris ke- {$this->rowId} ---> Nomor Transaksi bank terlalu pendek: '{$ntb}'");
            return;
        }

        $ntpn = $r['ntpn'];
        if (strlen(trim($ntpn)) < 16) {
            throw new \Exception("error pada baris ke- {$this->rowId} ---> Nomor Transaksi penerimaan negara terlalu pendek: '{$ntpn}'");
            return;
        }

        // parse dates
        $tgl_billing = $r['tgl_billing'];
        $tgl_ntpn = $r['tgl_ntpn'];

        // if both are not in yyyy-mm-dd format, might be wise to convert them
        $datePattern = "/\d{4}\-\d{2}\-\d{2}/i";
        if (!preg_match($datePattern, $tgl_billing)) {
            $ts = Date::excelToTimestamp($tgl_billing);
            $tgl_billing = date("Y-m-d", $ts);
        }

        if (!preg_match($datePattern, $tgl_ntpn)) {
            $ts = Date::excelToTimestamp($tgl_ntpn);
            $tgl_ntpn = date("Y-m-d", $ts);
        }

        $b = new Billing([
            'nomor' => (string) $kode_billing,
            'tanggal' => $tgl_billing,
            'ntb' => (string) $ntb,
            'ntpn' => (string) $ntpn,
            'tgl_ntpn' => $tgl_ntpn
        ]);

        // associate with bppm's payable
        if (!$bppm->payable) {
            // bppm kopong! throw error
            throw new \Exception("error pada baris ke- {$this->rowId} ---> BPPM nomor '{$no_bppm}' tidak memiliki dokumen dasar pembayaran!");
            return;
        }

        $b->billable()->associate($bppm->payable);

        $this->models[] = $b;
    }

    public function importToModels($filePath = null, ?string $disk = null, ?string $readerType = null)
    {
        $this->import($filePath, $disk, $readerType);

        return collect($this->models);
    }
}
