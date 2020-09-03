<?php

namespace App\Imports;

use App\PIBK;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;

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
            BeforeImport::class => function (BeforeImport $e) use ($pibk) {
                // let's read something
                $s = $e->getDelegate();

                dump($s);
            },

            AfterSheet::class => function (AfterSheet $e) use ($pibk) {
                $s = $e->sheet->getDelegate();
            }
        ];
    }
}
