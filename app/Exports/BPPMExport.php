<?php

namespace App\Exports;

use App\BPPM;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BPPMExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting
,WithEvents,ShouldAutoSize
{
    use Exportable;

    public function __construct()
    {
        $this->query = BPPM::query()->latest()->orderBy('id', 'desc');
        $this->rowCount = 1;
    }

    public function buildQuery(Request $r) {
        // grab some parameter
        $q = $r->get('q') ?? '';
        $from = $r->get('from');
        $to = $r->get('to');
        $deep = ($r->get('deep') == 'true');

        $billingStatus = $r->get('billing-status');
        // list all bppm, latest first
        $this->query = BPPM::query()
                ->when($q || $from || $to && !$deep, function ($q1) use ($q, $from, $to) {
                    $q1->byQuery($q, $from, $to);
                })
                ->when($deep, function ($q1) use ($q, $from, $to) {
                    $q1->orWhere(function ($q2) use ($q, $from, $to) {
                        $q2->deepQuery($q, $from, $to);
                    });
                })
                ->when($billingStatus, function ($q1) use ($billingStatus) {
                    $q1->whereHasBilling($billingStatus == 'true');
                })
                ->latest()
                ->orderBy('id','desc');
        return $this;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->query->get();
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => 'yyyy-mm-dd',
            'C' => '@',
            'D' => '#0',
            'E' => 'yyyy-mm-dd',
            'F' => '@',
            'H' => '#0',
            'I' => '#0',
            'J' => '@',
            'K' => '@',
            'L' => '#0',
            
            'M' => '#,##0.00',
            'N' => '#,##0.00',
            'O' => '#,##0.00',
            'P' => '#,##0.00',
            'Q' => '#,##0.00',
            'R' => '#,##0.00',
            'S' => '#,##0.00',

            'T' => '#0',
            'U' => 'yyyy-mm-dd',
            'V' => '#0',
            'W' => '#0',
            'X' => 'yyyy-mm-dd'
        ];
    }

    public function registerEvents(): array
    {
        $dataCount = $this->query->count();

        return [
            AfterSheet::class => function (AfterSheet $e) use ($dataCount) {
                $s = $e->sheet->getDelegate();

                ++$dataCount;

                $s->getStyle("T2:X{$dataCount}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => "FFFF00"]
                    ]
                ]);
            }
        ];
    }

    public function map($b): array
    {
        $p = $b->payable->payer;
        $billing = $b->payable->billing[0] ?? null;
        $t = $b->payable->pungutan;

        Collection::macro('groupReduce', function($kode) {
            return $this->where('jenisPungutan.kode', $kode)
                        ->reduce(function ($acc, $e) {
                            return $acc + $e->bayar;
                        }, 0);
        });

        ++$this->rowCount;

        return [
            $b->nomor_lengkap,
            $b->tgl_dok,

            $b->payable->jenis_dokumen_lengkap,
            ($b->payable->nomor_lengkap),
            $b->payable->tgl_dok,

            $p['nama'],
            $p['jenis_identitas'],
            ($p['no_identitas']),
            ($p['npwp']),
            (string) $p['alamat'],

            (string) $b->pejabat->name,
            ($b->pejabat->nip),

            $t->groupReduce('BM') ?? 0, //bm
            $t->groupReduce('BMTP') ?? 0, //bmtp
            $t->groupReduce('PPN') ?? 0, //ppn
            $t->groupReduce('PPh') ?? 0, //pph
            $t->groupReduce('PPnBM') ?? 0, //ppnbm
            $t->groupReduce('DA_PAB') ?? 0, //denda
            "=SUM(M{$this->rowCount}:R{$this->rowCount})", //total

            ($billing->nomor ?? ''),
            $billing->tanggal ?? '',
            ($billing->ntb ?? ''),
            ($billing->ntpn ?? ''),
            $billing->tgl_ntpn ?? ''
        ];
    }

    public function headings(): array
    {
        return [
            'nomor_bppm',
            'tgl_bppm',

            'jenis_dok',
            'nomor_dok',
            'tgl_dok',

            'nama_pembayar',
            'jenis_identitas',
            'nomor_identitas',
            'npwp',
            'alamat',

            'nama_pejabat',
            'nip_pejabat',

            'BM',
            'BMTP',
            'PPN',
            'PPh',
            'PPnBM',
            'DENDA',
            'TOTAL',

            'kode_billing',
            'tgl_billing',
            'ntb',
            'ntpn',
            'tgl_ntpn'
        ];
    }
}
