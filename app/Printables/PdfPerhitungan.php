<?php

namespace App\Printables;

use Fpdf\Fpdf;

class PdfPerhitungan extends Fpdf {

    protected $simulasi_hitung  = [];
    protected $nama_pejabat = 'Tri Mulyadi Wibowo';
    protected $nip_pejabat  = '199103112012101001';

    public function __construct(array $dataPerhitungan)
    {
        parent::__construct('L', 'mm', 'A4');
        // set for all pages, MUST UNSET FOR THE LAST PAGE
        $this->SetAutoPageBreak(true, 40);

        $this->simulasi_hitung = $dataPerhitungan;
    }

    public function Footer()
    {
        $this->SetFont('Arial', '', 8);

        // draw nama pdtt
    }

    // Add initial Page
    public function generatePDF() {
        
    }
}