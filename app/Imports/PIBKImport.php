<?php

namespace App\Imports;

use App\PIBK;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PIBKImport implements WithMultipleSheets
{
    protected $pibk;

    public function __construct()
    {
        $this->pibk = new PIBK();
    }

    public function sheets(): array
    {
        return [
            'HEADER' => new PIBKHeaderImport($this->pibk)
        ];
    }

    public function getPIBK() {
        return $this->pibk;
    }
}
