<?php
namespace App\Printables;

use App\CD;
use Fpdf\Fpdf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PdfLembarHitungCD extends Fpdf {
    protected $cd = null;

    public function __construct(CD $cd)
    {
        $this->cd   = $cd;

        parent::__construct('L', 'mm', 'A4');
        $this->AliasNbPages();
        $this->SetAutoPageBreak(true);
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

        if (!$this->cd->sspcp) {
            throw new BadRequestHttpException("CD belum ditetapkan!");
        }

        // feed data
        $data = $this->cd->simulasi_pungutan;

        $sspcp = $this->cd->sspcp;

        // var_dump($data);
        // print_r($data);
        $nilai_impor = $data['total_bm'] + $data['data_pembebasan']['nilai_dasar_perhitungan'];
        $nama_pejabat   = $sspcp->nama_pejabat;
        $nama_penumpang = $this->cd->penumpang->nama;

        $nomor_lengkap_dok  = $this->cd->nomor_lengkap_dok;
        $tgl_dok        = $this->cd->tgl_dok;

        // set reference
        $pdf = $this;

        $pdf->AddPage();

        // Print Title
        $this->font('B');
        $pdf->Cell(0, 4, 'LEMBAR PERHITUNGAN PUNGUTAN BEA MASUK DAN PAJAK', 0, 1, 'C');

        $this->font('BI');
        $pdf->Cell(0, 4, 'TAX AND DUTY CALCULATION SHEET', 0, 1, 'C');

        $this->font('B');
        $pdf->Cell(0, 4, "Halaman (page) {$pdf->PageNo()} / " . '{nb}', 0, 1, 'R');

        $pdf->Cell(0, 4, "Customs Declaration no. : {$nomor_lengkap_dok}        Date: {$tgl_dok}", 0, 1);

        // draw calculation table
        $this->font();

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // No.
        $pdf->MultiCell(7, 8, 'No.', 0, 'L');

        $pdf->Rect($row_x, $row_y, 7, $pdf->GetY()-$row_y);

        // Uraian Barang
        $pdf->SetXY($row_x + 7, $row_y);
        $pdf->Cell(63, 4, 'Uraian Barang', 0, 2);

        $this->font('I');
        $pdf->Cell(63, 4, 'Goods Description', 0, 2);

        $pdf->Rect($row_x + 7, $row_y, 63, $pdf->GetY()-$row_y);

        // (1) Cost/FOB
        $pdf->SetXY($row_x + 70, $row_y);
        $this->font();
        $pdf->MultiCell(22.5, 8, '(1) Cost/FOB', 0);

        $pdf->Rect($row_x + 70, $row_y, 22.5, $pdf->GetY()-$row_y);

        // (2) Insurance
        $pdf->SetXY($row_x + 70 + 22.5, $row_y);
        $this->font();
        $pdf->MultiCell(22.5, 8, '(2) Insurance', 0);

        $pdf->Rect($row_x + 70 + 22.5, $row_y, 22.5, $pdf->GetY()-$row_y);

        // (3) Freight
        $pdf->SetXY($row_x + 70 + 22.5 + 22.5, $row_y);
        $this->font();
        $pdf->MultiCell(22.5, 8, '(3) Freight', 0);

        $pdf->Rect($row_x + 70 + 22.5 + 22.5, $row_y, 22.5, $pdf->GetY()-$row_y);

        // (4) CIF: [(1)+(2)+(3)]
        $pdf->SetXY($row_x + 70 + 22.5 + 22.5 + 22.5, $row_y);
        $this->font();
        $pdf->MultiCell(25, 4, '(4) CIF: [(1)+(2)+(3)]', 0, 'L');

        $pdf->Rect($row_x + 70 + 22.5 + 22.5 + 22.5, $row_y, 25, $pdf->GetY()-$row_y);

        // Valuta
        $pdf->SetXY($row_x + 70 + 22.5 + 22.5 + 22.5 + 25, $row_y);
        $this->font();
        $pdf->Cell(14.5, 4, 'Valuta', 0, 2);
        $this->font('I');
        $pdf->Cell(14.5, 4, 'Currency', 0, 2);

        $pdf->Rect($row_x + 70 + 22.5 + 22.5 + 22.5 + 25, $row_y, 14.5, $pdf->GetY()-$row_y);

        // (5) NDPBM
        $pdf->SetXY($row_x + 70 + 22.5 + 22.5 + 22.5 + 25 + 14.5, $row_y);
        $this->font();
        $pdf->Cell(4, 4, '(5)');
        $pdf->Cell(25, 4, 'NDPBM', 0, 2);
        $this->font('I');
        $pdf->Cell(25, 4, 'Tax Currency Rate', 0, 2);

        $pdf->Rect($row_x + 70 + 22.5 + 22.5 + 22.5 + 25 + 14.5, $row_y, 30, $pdf->GetY()-$row_y);

        // (6) Nilai Pabean
        $pdf->SetXY($row_x + 70 + 22.5 + 22.5 + 22.5 + 25 + 14.5 + 30, $row_y);

        $row_x  = $pdf->GetX();
        $this->font();
        $pdf->Cell(4, 4, '(6)');
        $pdf->Cell(0, 4, 'Nilai Pabean (Rp): [(4)x(5)]', 0, 2);
        $this->font('I');
        $pdf->Cell(0, 4, 'Customs Value', 0, 0);

        $max_x = $pdf->GetX();
        $pdf->Ln();

        $last_col_width = $max_x-$row_x;

        $pdf->Rect($row_x, $row_y, $max_x-$row_x, $pdf->GetY()-$row_y);

        // render data barang here
        $no = 1;    // number starts from 1

        foreach($data['data_perhitungan'] as $d) {
            $this->font();

            // print_r($d[0]);
            // record row_x row_y
            $row_x  = $pdf->GetX();
            $row_y  = $pdf->GetY();

            // no
            $pdf->SetXY($row_x, $row_y);
            $pdf->Cell(7, 4, $no, 0, 0);

            // Uraian Barang
            $pdf->SetXY($row_x + 7, $row_y);
            $pdf->MultiCell(63, 4, $d['long_description'], 0, 'L');

            // record y every time we write data
            $max_y = $pdf->GetY();

            // FOB
            $pdf->SetXY($row_x + 70, $row_y);
            $pdf->Cell(22.5, 4, number_format($d['fob'], 2), 0, 0, 'R');

            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());

            // Insurance
            // $pdf->SetXY($row_x + 92.5, $row_y);
            $pdf->Cell(22.5, 4, number_format($d['insurance'], 2), 0, 0, 'R');

            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());
            
            // Freight
            // $pdf->SetXY($row_x + 92.5 + 22.5, $row_y);
            $pdf->Cell(22.5, 4, number_format($d['freight'], 2), 0, 0, 'R');

            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());
            
            // CIF
            // $pdf->SetXY($row_x + 92.5 + 22.5 + 22.5, $row_y);
            $pdf->Cell(25, 4, number_format($d['cif'], 2), 0, 0, 'R');

            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());
            
            // Valuta
            // $pdf->SetXY($row_x + 92.5 + 22.5 + 22.5 + 25, $row_y);
            $pdf->Cell(14.5, 4, $d['valuta'], 0, 0, 'R');

            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());

            // NDPBM
            // $pdf->SetXY($row_x + 92.5 + 22.5 + 22.5 + 25 + 14.5)
            $pdf->Cell(30, 4, number_format($d['ndpbm'], 2), 0, 0, 'R');
            
            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());
            
            // Nilai Pabean
            // $pdf->SetXY($row_x + 92.5 + 22.5 + 22.5 + 25 + 14.5)
            $pdf->Cell($last_col_width, 4, number_format($d['nilai_pabean'], 2), 0, 1, 'R');
            
            // record y every time we write data
            $max_y = max($max_y, $pdf->GetY());

            // Draw Rectangle nao
            $pdf->Rect($row_x, $row_y, 7, $max_y-$row_y);   // no
            $pdf->Rect($row_x + 7, $row_y, 63, $max_y-$row_y);   // uraian barang
            $pdf->Rect($row_x + 70, $row_y, 22.5, $max_y-$row_y);   // Cost
            $pdf->Rect($row_x + 92.5, $row_y, 22.5, $max_y-$row_y);   // Insurance
            $pdf->Rect($row_x + 115, $row_y, 22.5, $max_y-$row_y);   // Freight
            $pdf->Rect($row_x + 137.5, $row_y, 25, $max_y-$row_y);   // CIF
            $pdf->Rect($row_x + 162.5, $row_y, 14.5, $max_y-$row_y);   // Valuta
            $pdf->Rect($row_x + 177, $row_y, 30, $max_y-$row_y);   // Valuta
            $pdf->Rect($row_x + 207, $row_y, $last_col_width, $max_y-$row_y);   // Nilai Pabean

            
            // for next line, set cursor
            $pdf->SetY($max_y);

            $no++;

            // $pdf->Ln();
        }

        // total
        $this->font('B');
        $pdf->Cell(207, 4, '(7) Total', 1, 0, 'C');

        // Total Nilai Pabean
        $total_nilai_pabean = array_reduce($data['data_perhitungan'], function ($acc, $e) { return $acc + $e['nilai_pabean']; }, 0);

        $pdf->Cell(0, 4, number_format($total_nilai_pabean, 2), 1, 1, 'R');

        // add line for beautiful output
        $pdf->Ln(2);

        // Pembebasan
        $this->font();

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // (8) Pembebasan
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, '(8) Pembebasan', 0, 2);
        $this->font('I');
        $pdf->Cell(63, 4, 'De minimis value', 0, 0);

        // value of Pembebasan
        $this->font();
        $pdf->Cell(22.5, 4, number_format($data['data_pembebasan']['nilai_pembebasan'], 2), 0, 0, 'R');

        // valuta of pembebasan
        $pdf->Cell(22.5, 4, $data['data_pembebasan']['valuta'], 0, 0, 'C');

        // x
        $pdf->Cell(47.5, 4, 'x', 0, 0, 'C');

        // kurs ndpbm
        $pdf->SetX($row_x + 7 + 63 + 22.5 + 22.5 + 22.5 + 25 + 14.5 );
        $pdf->Cell(30, 4, number_format($data['data_pembebasan']['ndpbm'], 2), 0, 0, 'R');

        // Nilai Pembebasan Rp
        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['data_pembebasan']['nilai_pembebasan_rp'], 2), 0, 1, 'R');

        $pdf->Text(297-8.5, $pdf->GetY() - 1.5, '-');
        // $pdf->Write(4, '-');


        // Draw the underline + minus sign
        // $pdf->Text(297-10, $row_y, '(-)');

        // $pdf->Ln();
        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $pdf->Line($row_x, $row_y, 287, $row_y);


        // Nilai Dasar Perhitungan
        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        $pdf->SetX($row_x + 7);
        $this->font();
        $pdf->Cell(63, 4, '(9) Nilai dasar perhitungan BM + Pajak: [(7)-(8)]', 0, 2);
        $this->font('I');
        $pdf->Cell(63, 4, 'Base Value for Tax and Duty calculation', 0, 0);

        // Bold
        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['data_pembebasan']['nilai_dasar_perhitungan'], 2), 0, 1, 'R');

        $pdf->Line($pdf->GetX(), $pdf->GetY(), 287, $pdf->GetY());

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // Title
        $pdf->Ln();
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, 'BEA MASUK DAN PAJAK', 0, 2);
        $this->font('BI');
        $pdf->Cell(63, 4, 'TAX AND DUTY', 0, 1);

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // 10. Bea Masuk
        $this->font();
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, "(10) Bea Masuk: [(9) x 10%]", 0, 2);
        $this->font('I');
        $pdf->Cell(63, 4, 'Customs Duty', 0, 0);

        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['total_bm'], 2), 0, 1, 'R');

        $pdf->Line($row_x, $pdf->GetY(), 287, $pdf->GetY());

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // 11. Nilai Impor
        $this->font('',[255, 0, 0]);
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, "(11) Nilai Impor: [(9)+(10)]", 0, 2);
        $this->font('I',[255, 0, 0]);
        $pdf->Cell(63, 4, 'Import Value', 0, 0);

        $this->font('B',[255, 0, 0]);

        $pdf->SetX($row_x + 7 + 63 + 22.5 + 22.5 + 22.5 + 25 + 14.5 );
        $pdf->Cell(30, 4, number_format($nilai_impor, 2), 0, 1, 'R');

        $pdf->Line($row_x, $pdf->GetY(), 287, $pdf->GetY());

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // 12. PPN
        $this->font();
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, "(12) PPN: [(11) x 10%]", 0, 2);
        $this->font('I');
        $pdf->Cell(63, 4, 'Value Added Tax', 0, 0);

        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['total_ppn'], 2), 0, 1, 'R');

        $pdf->Line($row_x, $pdf->GetY(), 287, $pdf->GetY());

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // 13. PPh
        $this->font();
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, "(13) PPh: [(11) x ". number_format($data['pph_tarif'], 2) ."%]", 0, 2);
        $this->font('I');
        $pdf->Cell(63, 4, 'Value Added Tax', 0, 0);

        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['total_pph'], 2), 0, 1, 'R');

        $pdf->Line($row_x, $pdf->GetY(), 287, $pdf->GetY());

        // record row_x row_y
        $row_x  = $pdf->GetX();
        $row_y  = $pdf->GetY();

        // 14. Total
        $this->font('B');
        $pdf->SetX($row_x + 7);
        $pdf->Cell(63, 4, "Total Bea Masuk dan Pajak", 0, 2);
        $this->font('BI');
        $pdf->Cell(63, 4, 'Total Duty and Tax', 0, 0);

        $this->font('B');
        $pdf->Cell(0, 4, number_format($data['total_bm_pajak'], 2), 0, 1, 'R');
        $pdf->Line($row_x, $pdf->GetY(), 287, $pdf->GetY());
        $pdf->Line($row_x, $pdf->GetY() + 1, 287, $pdf->GetY() + 1);

        $pdf->Ln();


        // nama pejabat dan penumpang
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