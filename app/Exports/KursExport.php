<?php

namespace App\Exports;

use App\Kurs;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KursExport implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    // protected $kursDate;

    public function __construct()
    {
        $this->kursDate = date('Y-m-d');
    }

    public function perTanggal($tgl)
    {
        $this->kursDate = $tgl ?? date('Y-m-d');

        return $this;
    }

    function query()
    {
        return Kurs::perTanggal($this->kursDate);
    }

    public function map($k): array
    {
        return
            [
                $k->kode_valas,
                $k->kurs_idr,
                $k->tanggal_awal,
                $k->tanggal_akhir
            ];
    }

    public function headings(): array
    {
        return [
            'kode_valas',
            'kurs_idr',
            'tanggal_awal',
            'tanggal_akhir'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0.0000'
        ];
    }
}
