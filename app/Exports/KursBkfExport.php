<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KursBkfExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    public function __construct()
    {
        $kurs = grabKursData();

        $arr = [];
        foreach ($kurs['data'] as $valuta => $nilai) {
            $arr[] = [
                $valuta, $nilai, $kurs['dateStart'], $kurs['dateEnd']
            ];
        }

        $this->kursData = $arr;
    }

    public function array():array {
        return $this->kursData;
    }

    public function headings(): array
    {
        return [
            'Kode Valuta',
            'Nilai Tukar',
            'Tanggal Awal',
            'Tanggal Akhir'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0.0000'
        ];
    }
}
