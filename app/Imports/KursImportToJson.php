<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KursImportToJson implements WithHeadingRow, WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            0   => new KursImportToJson()
        ];
    }
}
