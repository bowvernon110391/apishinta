<?php
namespace App\Printables;

use App\CD;
use Fpdf\Fpdf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PdfLembarHitungCD extends PDF_MC_Table {
    protected $cd = null;

    public static $dict = [
        'BM'=> [
            'Bea Masuk',
            'Import Duty'
        ],
        'BMI'=> [
            'Bea Masuk Imbalan',
            'Reciprocal Duty'
        ],
        'BMAD'=> [
            'Bea Masuk Anti Dumping',
            'Duty for Anti Dumping'
        ],
        'BMTP'=> [
            'Bea Masuk Tindak Pengamaman',
            'Duty for Protection'
        ],
        'PPN'=> [
            'PPN Impor',
            'Value Added Tax'
        ],
        'PPh'=> [
            'PPh Impor',
            'Income Tax'
        ],
        'PPnBM'=> [
            'PPnBM Impor',
            'Luxury Goods Tax'
        ]
    ];

    public function __construct(CD $cd)
    {
        $this->cd   = $cd;

        parent::__construct('L', 'mm', 'A4');
        $this->AliasNbPages();
        $this->SetAutoPageBreak(true, 5);
    }

    protected function font($style = '', $color=[0,0,0]) {
        $pdf = $this;
        $pdf->SetFont('Arial', $style, 8);
        $pdf->SetTextColor($color[0], $color[1], $color[2]);
    }

    public function generateFirstPage() {
        if (!$this->cd) {
            throw new BadRequestHttpException("CD data not provided");
        }

        if (!$this->cd->is_locked) {
            throw new BadRequestHttpException("CD belum ditetapkan!");
        }

        // feed data
        $data = $this->cd->computePungutanCdPembebasanProporsional();

        $lock = $this->cd->lock;
        $petugas = $lock->petugas;

        // $nilai_impor = $data['nilai_impor'];
        $nama_pejabat   = $petugas->name;
        $nama_penumpang = $this->cd->penumpang->nama;
        $nomor_paspor = $this->cd->penumpang->no_paspor;
        $npwp = $this->cd->npwp ?? '-';

        $nomor_lengkap_dok  = $this->cd->nomor_lengkap_dok;
        $tgl_dok        = $this->cd->tgl_dok;

        // set reference
        $pdf = $this;

        $pdf->AddPage();

        // surround with rectangle?
        $pdf->Rect(4, 4, 297-8, 210-8);

        // 3 baris kop?
        $this->font("B");
        $pdf->MultiCell(0, 4, "KEMENTERIAN KEUANGAN REPUBLIK INDONESIA\nDIREKTORAT JENDERAL BEA DAN CUKAI\nKANTOR PELAYANAN UTAMA BEA DAN CUKAI SOEKARNO HATTA", 0, 'L');

        // Print Title
        $this->font('B');
        $pdf->Cell(0, 4, 'RINCIAN PERHITUNGAN PUNGUTAN BEA MASUK DAN PAJAK', 0, 1, 'C');

        $this->font('BI');
        $pdf->Cell(0, 4, 'TAX AND DUTY CALCULATION DETAILS', 0, 1, 'C');

        $this->font('B');

        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $pdf->SetXY(287.0/2.0, $row_y);
        $pdf->Cell(0, 4, "Halaman (page) {$pdf->PageNo()} / " . '{nb}', 0, 0, 'R');
        $pdf->SetXY(0, $row_y);

        $pdf->font();
        $pdf->Cell(0, 4, "CUSTOMS DECLARATION No. : {$nomor_lengkap_dok}        DATE: {$tgl_dok}", 0,1,'C');

        // penumpang info
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $pdf->font('B');
        $pdf->Cell(0, 4, 'PASSENGER', 0, 1);

        // NAMA PENUMPANG
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $pdf->font();
        $pdf->SetXY($row_x + 7, $row_y);
        $pdf->Cell(32, 4, 'NAME', 0, 0);
        $pdf->Cell(8, 4, ':', 0, 0, 'C');
        $pdf->Cell(0, 4, $nama_penumpang, 0, 1);
        // NPWP PENUMPANG
        // NPWP PENUMPANG
        $pdf->SetXY($row_x + 157, $row_y);
        $pdf->Cell(32, 4, 'NPWP', 0, 0);
        $pdf->Cell(8, 4, ':', 0, 0, 'C');
        $pdf->Cell(0, 4, $npwp, 0, 1);
        // IDENTITAS PENUMPANG
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();
        $pdf->SetXY($row_x + 7, $row_y);
        $pdf->Cell(32, 4, 'IDENTITY NUMBER', 0, 0);
        $pdf->Cell(8, 4, ':', 0, 0, 'C');
        $pdf->Cell(0, 4, $nomor_paspor, 0, 1);

        // draw calculation table
        $this->font();

        $widths = [
            7,
            63,
            22.5,
            22.5,
            22.5,
            22.5,
            16,
            25,
            37.5,
            40
        ];

        $this->SetWidths($widths);

        $this->SetAligns([
            'V',
            'L',
            'V',
            'V',
            'V',
            'C',
            'C',
            'C',
            'V',
            'C'
        ]);

        $this->Row([
            "No.",
            "Uraian Barang\n(Goods Description)",
            "(1) Cost/FOB",
            "(2) Insurance",
            "(3) Freight",
            "CIF\n(1+2+3)",
            "Valuta\n(Currency)",
            "Pembebasan\n(Deminimis)",
            "HS Code and Tarriffs",
            "Bea Masuk dan Pajak\n(Duty and Tax)"
        ]);

        // per detail barang
        $this->SetAligns([
            'L',
            'L',
            'R',
            'R',
            'R',
            'R',
            'C',
            'R',
            'L',
            'R'
        ]);

        $no = 1;
        foreach ($data['barang'] as $d) {
            // format output
            $cif = $d['valuta']. ' ' . number_format($d['cif']) . "\n= " . "Rp " . number_format($d['nilai_pabean'],2);
            $pembebasan = "USD " . number_format($d['pembebasan'], 2) . "\n= Rp " . number_format($d['pembebasan_idr'], 2);
            $hs_code_tarif = "{$d['hs_raw_code']}\n";
            $duty_tax = "";

            foreach ($d['tarif'] as $kode => $t) {
                $hs_code_tarif.= "{$kode}: {$t['tarif']}";
                if (substr($kode,0,2) == 'BM') {
                    if ($t['jenis'] == 'ADVALORUM') {
                        $hs_code_tarif .= '%';
                    }
                    $hs_code_tarif .= " ({$t['jenis']})";
                } else {
                    $hs_code_tarif .= '%';
                }
                
                $hs_code_tarif .= "\n";
            }

            foreach ($d['pungutan'] as $p) {
                $duty_tax .= "{$p->jenisPungutan->kode}: Rp " . number_format($p->bayar,2);
                $duty_tax .= "\n";
            }

            $row = [
                $no++,
                $d['uraian_print'],
                $d['fob'],
                $d['insurance'],
                $d['freight'],
                $cif,
                '1 ' . $d['valuta'] . "\n= Rp " . number_format($d['ndpbm'],2),
                $pembebasan,
                $hs_code_tarif,
                $duty_tax
            ];

            $this->Row($row);
        }

        $this->CheckPageBreak(8);
        $this->Ln();
        $this->font('B');
        $this->SetX($this->GetX()+7);
        $this->Cell(0,4,"BEA MASUK DAN PAJAK",0,1);
        $this->font('BI');
        $this->SetX($this->GetX()+7);
        $this->Cell(0,4,"DUTY AND TAX SUMMARY",0,1);
        
        $total_h = 4 * count($data['pungutan']['bayar']) * 2 + 8;

        $this->CheckPageBreak($total_h);

        // spawn data
        foreach ($data['pungutan']['bayar'] as $kode => $total) {
            $this->SetX($this->GetX()+7);
            $w = $this->GetStringWidth(PdfLembarHitungCD::$dict[$kode][0]);
            $this->font();
            $this->Cell($w, 4, PdfLembarHitungCD::$dict[$kode][0], 0, 1);

            $this->SetX($this->GetX()+7);
            $w = $this->GetStringWidth(PdfLembarHitungCD::$dict[$kode][1]);
            $this->font('I');
            $this->Cell($w, 4, PdfLembarHitungCD::$dict[$kode][1], 0, 0);
            
            $this->SetX($this->GetX()+$w);
            $this->font('B');
            $this->Cell(0, 4, number_format($total,2),0,1,'R');
            $this->Line($this->GetX(), $this->GetY(), $this->GetPageWidth()-$this->rMargin, $this->GetY());
        }

        // Total
        $total_pungutan = array_reduce($data['pungutan']['bayar'], function($acc,$e){ return $acc+$e; },0.0);

        $this->SetX($this->GetX()+7);
        $this->font('B');
        $this->Cell(0, 4, 'Total Bea Masuk dan Pajak', 0, 1);
        $this->SetX($this->GetX()+7);
        $this->font('BI');
        $this->Cell(50, 4, 'Total Duty and Tax', 0, 0);
        $this->font('B');
        $this->Cell(0, 4, number_format($total_pungutan,2),0,1,'R');
        $this->Line($this->GetX(), $this->GetY(), $this->GetPageWidth()-$this->rMargin, $this->GetY());
        $this->Line($this->GetX(), $this->GetY()+1, $this->GetPageWidth()-$this->rMargin, $this->GetY()+1);

        $pdf->Ln();


        // nama pejabat dan penumpang
        $this->CheckPageBreak(16);
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $this->font();
        $pdf->Cell(287/2.0, 4, 'Pejabat Bea Cukai', 0, 0, 'C');
        $pdf->Cell(0, 4, 'Penumpang', 0, 1, 'C');

        $this->font('I');
        $pdf->Cell(287/2.0, 4, 'Customs Officer', 0, 0, 'C');
        $pdf->Cell(0, 4, 'Passenger', 0, 1, 'C');

        // double line?
        $pdf->Ln(8);

        // real name n shiet
        $this->font();
        $pdf->Cell(287/2.0, 4, "({$nama_pejabat})", 0, 0, 'C');
        $pdf->Cell(0, 4, "({$nama_penumpang})", 0, 1, 'C');
    }
}