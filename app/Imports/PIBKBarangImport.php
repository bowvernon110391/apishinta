<?php

namespace App\Imports;

use App\DetailBarang;
use App\Gudang;
use App\Kurs;
use App\Lokasi;
use App\PIBK;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PIBKBarangImport implements WithEvents
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
            AfterSheet::class => function (AfterSheet $e) use ($pibk) {
                $s = $e->sheet->getDelegate();

                if (App::runningInConsole())
                    echo "Reading Sheet Barang\n";

                $kd_lokasi = $s->getCell('B2')->getValue();
                $barang_di_gudang = $s->getCell('B3')->getValue();
                $koli = (int) $s->getCell('B4')->getValue();

                if ($barang_di_gudang == 'YA') {
                    $lokasi = Gudang::where('kode', $kd_lokasi)->first();
                    if (!$lokasi) {
                        throw new \Exception("Gudang {$kd_lokasi} tidak valid!");
                    }
                } else {
                    $lokasi = Lokasi::where('kode', $kd_lokasi)->first();
                    if (!$lokasi) {
                        throw new \Exception("kode lokasi {$kd_lokasi} tidak valid!");
                    }
                }

                $pibk->lokasi()->associate($lokasi);
                // $pibk->lokasi_type = get_class($lokasi);
                $pibk->koli = $koli;

                // baca detail barang. start dari row 8
                $rowStart = 8;

                $detailBarang = [];

                while (true) {
                    $no = $s->getCell("A{$rowStart}")->getValue();
                    if (!$no) break;

                    $uraian = $s->getCell("B{$rowStart}")->getValue();
                    $jumlah_kemasan = (float) $s->getCell("C{$rowStart}")->getValue();
                    $jenis_kemasan = $s->getCell("D{$rowStart}")->getValue();
                    $jumlah_satuan = $s->getCell("E{$rowStart}")->getValue();
                    if ($jumlah_satuan) {
                        $jumlah_satuan = (float) $jumlah_satuan;
                    }
                    $jenis_satuan = $s->getCell("F{$rowStart}")->getValue();

                    $kode_valas = $s->getCell("G{$rowStart}")->getValue();

                    $fob = (float) $s->getCell("H{$rowStart}")->getValue();
                    $insurance = (float) $s->getCell("I{$rowStart}")->getValue();
                    $freight = (float) $s->getCell("J{$rowStart}")->getValue();
                    
                    $brutto = (float) $s->getCell("K{$rowStart}")->getValue();
                    $netto = $s->getCell("L{$rowStart}")->getValue();
                    if ($netto) {
                        $netto = (float) $netto;
                    }
                    
                    // $tarif_pph_override = $s->getCell("M{$rowStart}")->getValue();
                    // $tarif_bm_override = $s->getCell("N{$rowStart}")->getValue();

                    // spawn it
                    $kurs = Kurs::perTanggal(date('Y-m-d'))->where('kode_valas', $kode_valas)->first();
                    if (!$kurs) {
                        throw new \Exception("Kurs {$kode_valas} per tanggal hari ini tidak ditemukan. Cek data kurs atau coba update data kurs dlu!");
                    }

                    $detailBarang[] = new DetailBarang([
                        'uraian' => $uraian,
                        'jumlah_kemasan' => $jumlah_kemasan,
                        'jenis_kemasan' => $jenis_kemasan,
                        'jumlah_satuan' => $jumlah_satuan,
                        'jenis_satuan' => $jenis_satuan,
                        'hs_id' => null,
                        'fob' => $fob,
                        'insurance' => $insurance,
                        'freight' => $freight,
                        'brutto' => $brutto,
                        'netto' => $netto,
                        'kurs_id' => $kurs->id
                    ]);

                    ++$rowStart;
                }

                // collect it
                $pibk->detailBarang = collect($detailBarang);
            }
        ];
    }
}
