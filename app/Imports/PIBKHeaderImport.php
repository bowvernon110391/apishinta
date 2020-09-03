<?php

namespace App\Imports;

use App\Dokkap;
use App\Penumpang;
use App\PIBK;
use App\PJT;
use App\ReferensiJenisDokkap;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PIBKHeaderImport implements WithEvents
{
    protected $pibk;

    public function __construct(PIBK $p)
    {
        $this->pibk = $p;    
    }
    
    public function registerEvents(): array
    {
        $pibk = $this->pibk;
        return [
            /* BeforeImport::class => function (BeforeImport $e) use ($pibk) {
                // let's read something
                $s = $e->getDelegate();

                dump($s);
            }, */

            AfterSheet::class => function (AfterSheet $e) use ($pibk) {
                $s = $e->sheet->getDelegate();

                if (App::runningInConsole())
                    echo "Reading Sheet HEADER\n";
                // dump($s);

                $pibk->tgl_dok = date('Y-m-d');

                $pibk->importir = new Penumpang();
                $pibk->importir_type = Penumpang::class;

                $p = $pibk->importir;
                $p->nama = $s->getCell('B2')->getValue();
                $p->no_paspor = trim( preg_replace('/[^0-9A-Za-z]./i', '', $s->getCell('B3')->getValue() ) );
                $p->pekerjaan = '-';
                $p->nik = $s->getCell('B5')->getValue();
                $p->kd_negara_asal = $s->getCell('B6')->getValue();
                $p->email = $s->getCell('B7')->getValue();
                $p->phone = $s->getCell('B8')->getValue();

                $pjt = PJT::where('nama', $s->getCell('B10')->getValue())->first();

                // if pjt is valid, associate with it
                if ($pjt) {
                    $pibk->pemberitahu()->associate($pjt);
                }

                // npwp + validasi
                $npwp = $s->getCell('B14')->getValue();
                $npwp = preg_replace('/[^0-9]/i', '', $npwp);
                $pibk->npwp = $npwp;
                if (strlen($pibk->npwp) != 0 && strlen($pibk->npwp) != 15) {
                    throw new \Exception("antara isi NPWP atau kosongkan sama sekali. ({$pibk->npwp})length: " . strlen($pibk->npwp));
                }

                // alamat
                $pibk->alamat = $s->getCell('B15')->getValue();
                // no_flight
                $pibk->no_flight = trim($s->getCell('B16')->getValue());
                // kd_airline
                $pibk->kd_airline = substr($pibk->no_flight,0,2);
                // tgl_kedatangan
                $pibk->tgl_kedatangan = date('Y-m-d', Date::excelToTimestamp($s->getCell('B17')->getValue()));
                // kd_pelabuhan_asal
                $pibk->kd_pelabuhan_asal = $s->getCell('B18')->getValue();
                $pibk->kd_pelabuhan_tujuan = $s->getCell('B19')->getValue();
                // tarif_pph
                $pibk->tarif_pph = (float) $s->getCell('B20')->getValue();

                // no_bc11
                $pibk->no_bc11 = $s->getCell('B23')->getValue();
                $pibk->tgl_bc11 = $s->getCell('B24')->getValue();
                if ($pibk->tgl_bc11) {
                    $pibk->tgl_bc11 = date('Y-m-d', Date::excelToTimestamp($pibk->tgl_bc11));
                }
                $pibk->pos_bc11 = $s->getCell('B25')->getValue();
                $pibk->subpos_bc11 = $s->getCell('B26')->getValue();
                $pibk->subsubpos_bc11 = $s->getCell('B27')->getValue();

                // DOKKAP SECTION
                $dokkap = [];

                // dokkap: MAWB
                $rowStart = 31;
                
                // read until blank is found
                while (true) {
                    $jenis_dokkap = $s->getCell("A{$rowStart}")->getValue();

                    // blank found? bail
                    if (!strlen(trim($jenis_dokkap))) 
                        break;

                    // validate jenis_dokkap
                    $ref_jenis_dokkap = ReferensiJenisDokkap::where('nama', $jenis_dokkap)->first();
                    if (!$ref_jenis_dokkap) {
                        throw new \Exception("Jenis dokkap tidak valid: {$jenis_dokkap}");
                    }

                    $nomor_dokkap = (string) $s->getCell("B{$rowStart}")->getValue();
                    $tgl_dokkap = $s->getCell("C{$rowStart}")->getValue();
                    if ($tgl_dokkap) {
                        $tgl_dokkap = date('Y-m-d', Date::excelToTimestamp($tgl_dokkap));
                    }

                    $dokkap[] = new Dokkap([
                        'jenis_dokkap_id' => $ref_jenis_dokkap->id,
                        'nomor_lengkap_dok' => $nomor_dokkap,
                        'tgl_dok' => $tgl_dokkap
                    ]);

                    ++$rowStart;
                }

                // attach to pibk
                $pibk->dokkap = collect($dokkap);
            }
        ];
    }
}
