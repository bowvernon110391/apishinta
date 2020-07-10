<?php

namespace App\Printables;

use App\SPPB;
use Fpdf\Fpdf;

class PdfSPPB extends Fpdf
{
    public function __construct(SPPB $s)
    {
        $this->sppb = $s;

        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true);
    }

    // generate first page
    public function generateFirstPage()
    {
        $kop_a = "KEMENTERIAN KEUANGAN REPUBLIK INDONESIA ";
        $kop_b = "DIREKTORAT JENDERAL BEA DAN CUKAI ";
        $kop_c = "KANTOR PELAYANAN UTAMA BEA DAN CUKAI TIPE C SOEKARNO-HATTA";
        $kop_d = "";
        $kop1  = " SURAT PERSETUJUAN PENGELUARAN BARANG";

        $data = $this->sppb->gateable->data_sppb;

        $noidtahu = $data['pemberitahu']['npwp'];
        $nmTahu = strtoupper($data['pemberitahu']['nama']);
        $idterima = $data['importir']['npwp'];
        $nmterima = strtoupper($data['importir']['nama']);
        $lokasi = $this->sppb->lokasi->kode;
        $hawb = $data['hawb']['nomor'];
        $tglawb = $data['hawb']['tanggal'];
        $nmsarana = strtoupper($data['sarana_pengangkut']);
        $voy = $data['no_flight'];

        $bc11 = $data['no_bc11'];
        $tglbc = $data['tgl_bc11'];
        $kemas = $data['jumlah_jenis_kemasan'];
        $pos = $data['pos_bc11'];
        $subpos = $data['subpos_bc11'] . ' . ' . $data['subsubpos_bc11'];
        $berat = number_format($data['brutto'], 2);


        #sertakan library FPDF dan bentuk objek   
        $pdf = $this;
        //	$pdf->SetTopMargin(5);
        $pdf->AddPage();

        #judul
        $pdf->SetFont('Arial', 'B', '12');
        $pdf->SetXY(0, 33);
        $pdf->Cell(0, 5, $kop1, 0, 3, 'C');

        $pdf->SetFont('Arial', '', '10');
        $pdf->SetXY(15, 16);
        $pdf->Cell(0, 3.5, $kop_a, 0, 1, 'L');
        $pdf->SetXY(15, 20);
        $pdf->Cell(0, 3.5, $kop_b, 0, 1, 'L');
        $pdf->SetXY(15, 24);
        $pdf->Cell(0, 3.5, $kop_c, 0, 1, 'L');
        $pdf->SetXY(15, 28);
        $pdf->Cell(0, 3.5, $kop_d, 0, 1, 'L');

        #menggambar form tabel pib
        // $pdf->Rect(15,15,180,0.1);
        // $pdf->Rect(15,32,180,0.1);


        $pdf->Rect(15, 135, 85, 4);
        $pdf->Rect(21, 135, 20, 4);
        $pdf->Rect(54, 135, 20, 4);
        $pdf->Rect(15, 139, 85, 4);
        $pdf->Rect(21, 139, 20, 4);
        $pdf->Rect(54, 139, 20, 4);
        // $pdf->Rect(15, 143, 85, 20);    
        // 	$pdf->Rect(21, 143, 20, 20);  
        // 	$pdf->Rect(54, 143, 20, 20);	

        $pdf->Rect(100, 135, 95, 4);
        $pdf->Rect(106, 135, 22, 4);
        $pdf->Rect(143, 135, 25, 4);
        $pdf->Rect(100, 139, 95, 4);
        $pdf->Rect(106, 139, 22, 4);
        $pdf->Rect(143, 139, 25, 4);
        // $pdf->Rect(100, 143, 95, 20);    
        // 	$pdf->Rect(106, 143, 22, 20);  
        // 	$pdf->Rect(143, 143, 25, 20);

        $pdf->SetFont('Arial', '', '9');
        $pdf->SetXY(15, 135.5);
        $pdf->Cell(0, 3.5, 'No. ', '0', 0, 'L');
        $pdf->SetXY(23, 135.5);
        $pdf->Cell(0, 3.5, 'No. Peti ', '0', 0, 'L');
        $pdf->SetXY(42, 135.5);
        $pdf->Cell(0, 3.5, 'Ukuran ', '0', 0, 'L');
        $pdf->SetXY(54.3, 135.5);
        $pdf->Cell(0, 3.5, 'Penegahan ', '0', 0, 'L');
        $pdf->SetXY(75, 135.5);
        $pdf->Cell(0, 3.5, 'Keterangan ', '0', 0, 'L');

        $pdf->SetFont('Arial', '', '9');
        $pdf->SetXY(100, 135.5);
        $pdf->Cell(0, 3.5, 'No. ', '0', 0, 'L');
        $pdf->SetXY(110, 135.5);
        $pdf->Cell(0, 3.5, 'No. Peti', '0', 0, 'L');
        $pdf->SetXY(130, 135.5);
        $pdf->Cell(0, 3.5, 'Ukuran ', '0', 0, 'L');
        $pdf->SetXY(147, 135.5);
        $pdf->Cell(0, 3.5, 'Penegahan ', '0', 0, 'L');
        $pdf->SetXY(173, 135.5);
        $pdf->Cell(0, 3.5, 'Keterangan ', '0', 0, 'L');

        $pdf->SetXY(15.5, 139.3);
        $pdf->Cell(0, 3.5, '(1) ', '0', 0, 'L');
        $pdf->SetXY(27, 139.3);
        $pdf->Cell(0, 3.5, '(2) ', '0', 0, 'L');
        $pdf->SetXY(43, 139.3);
        $pdf->Cell(0, 3.5, '(3) ', '0', 0, 'L');
        $pdf->SetXY(60, 139.3);
        $pdf->Cell(0, 3.5, '(4) ', '0', 0, 'L');
        $pdf->SetXY(85, 139.3);
        $pdf->Cell(0, 3.5, '(5) ', '0', 0, 'L');

        $pdf->SetXY(100.5, 139.3);
        $pdf->Cell(0, 3.5, '(1) ', '0', 0, 'L');
        $pdf->SetXY(114, 139.3);
        $pdf->Cell(0, 3.5, '(2) ', '0', 0, 'L');
        $pdf->SetXY(133, 139.3);
        $pdf->Cell(0, 3.5, '(3) ', '0', 0, 'L');
        $pdf->SetXY(153, 139.3);
        $pdf->Cell(0, 3.5, '(4) ', '0', 0, 'L');
        $pdf->SetXY(178, 139.3);
        $pdf->Cell(0, 3.5, '(5) ', '0', 0, 'L');


        #tampilkan fixed text form sspcp	
        $pdf->SetFont('Arial', '', '10');
        $pdf->SetXY(0, 38);
        
        $pdf->Cell(0, 4, "No. {$this->sppb->nomor_lengkap_dok}    Tanggal: {$this->sppb->tgl_dok}", 0, 1, 'C');
        $pdf->Ln();

        $jenis_dok = $this->sppb->gateable->jenis_dokumen_lengkap;

        $pdf->SetX(15);
        $pdf->Cell(65, 4, "Nomor {$jenis_dok}", 0, 0, 'R');
        $pdf->Cell(3.5, 4, ':');
        $pdf->Cell(55, 4, $this->sppb->gateable->nomor_lengkap_dok, 0, 0);

        $pdf->Cell(16, 4, 'Tanggal : ', 0, 0);
        $pdf->Cell(0, 4, $this->sppb->gateable->tgl_dok, 0, 1);

        $startY = 60;

        $pdf->SetFont('Arial', '', '10');
        $pdf->SetXY(15, $startY);
        $pdf->Cell(0, 3.5, 'PEMBERITAHU ', '0', 0, 'L');
        $pdf->SetXY(65, $startY);
        $pdf->Cell(0, 3.5, '', '0', 0, 'L');
        $pdf->SetXY(20, $startY+4);
        $pdf->Cell(0, 3.5, 'NPWP ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+4);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+4);
        $pdf->Cell(0, 3.5, $noidtahu, '0', 1, 'L');
        $pdf->SetXY(20, $startY+8);
        $pdf->Cell(0, 3.5, 'Nama ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+8);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+8);
        $pdf->Cell(0, 3.5, $nmTahu, '0', 1, 'L');

        $pdf->SetXY(15, $startY+16);
        $pdf->Cell(0, 3.5, 'IMPORTIR ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+16);
        $pdf->Cell(0, 3.5, '', '0', 0, 'L');
        $pdf->SetXY(20, $startY+20);
        $pdf->Cell(0, 3.5, 'NPWP ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+20);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+20);
        $pdf->Cell(0, 3.5, $idterima, '0', 1, 'L');
        $pdf->SetXY(20, $startY+24);
        $pdf->Cell(0, 3.5, 'Nama ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+24);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+24);
        $pdf->Cell(0, 3.5, $nmterima, '0', 1, 'L');

        $pdf->SetXY(15, $startY+32);
        $pdf->Cell(0, 3.5, 'Lokasi Barang ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+32);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+32);
        $pdf->Cell(0, 3.5, $lokasi, '0', 1, 'L');
        //$pdf->SetXY(68,102); $pdf->Cell(0,3.5, $idtahu, '0', 1, 'L');
        $pdf->SetXY(15, $startY+36);
        $pdf->Cell(0, 3.5, 'No. Tgl. B/L/AWB ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+36);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+36);
        $pdf->Cell(0, 3.5, $hawb, '0', 1, 'L');
        $pdf->SetXY(105, $startY+36);
        $pdf->Cell(0, 3.5, "Tanggal : " . $tglawb, '0', 1, 'L');
        $pdf->SetXY(15, $startY+40);
        $pdf->Cell(0, 3.5, 'Sarana Pengangkut ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+40);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+40);
        $pdf->Cell(0, 3.5, $nmsarana, '0', 1, 'L');
        $pdf->SetXY(15, $startY+44);
        $pdf->Cell(0, 3.5, 'No. Voy/Flight ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+44);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+44);
        $pdf->Cell(0, 3.5, $voy, '0', 1, 'L');
        
        $pdf->SetXY(15, $startY+48);
        $pdf->Cell(0, 3.5, 'No. Tgl BC.1.1/BC ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+48);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+48);
        $pdf->Cell(0, 3.5, $bc11, '0', 1, 'L');
        $pdf->SetXY(95, $startY+48);
        $pdf->Cell(0, 3.5, "Tanggal : ". $tglbc, '0', 1, 'L');
        $pdf->SetXY(15, $startY+52);
        $pdf->Cell(0, 3.5, 'Jumlah/Jenis Kemasan ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+52);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(68, $startY+52);
        $pdf->Cell(0, 3.5, $kemas, '0', 1, 'L');
        $pdf->SetXY(15, $startY+56);
        $pdf->Cell(0, 3.5, 'Merek Kemasan ', '0', 0, 'L');
        $pdf->SetXY(65, $startY+56);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');


        $pdf->SetXY(135, $startY+48);
        $pdf->Cell(0, 3.5, 'Pos', '0', 0, 'L');
        $pdf->SetXY(145, $startY+48);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(148, $startY+48);
        $pdf->Cell(0, 3.5, $pos, '0', 1, 'L');
        $pdf->SetXY(158, $startY+48);
        $pdf->Cell(0, 3.5, 'Sub.Pos ', '0', 0, 'L');
        $pdf->SetXY(175, $startY+48);
        $pdf->Cell(0, 3.5, $subpos, '0', 1, 'L');
        $pdf->SetXY(135, $startY+52);
        $pdf->Cell(0, 3.5, 'Bruto ', '0', 0, 'L');
        $pdf->SetXY(145, $startY+52);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(148, $startY+52);
        $pdf->Cell(0, 3.5, $berat, '0', 1, 'L');
        $pdf->SetXY(160, $startY+52);
        $pdf->Cell(0, 3.5, 'Kgs ', '0', 0, 'L');

        $startY = 200;

        $pdf->SetXY(17, 190);
        $pdf->Cell(0, 3.5, 'Pejabat yang memeriksa dokumen', '0', 0, 'L');
        $pdf->SetXY(17, 208);
        // $pdf->Cell(0, 3.5, 'Tanda tangan ', '0', 0, 'L');
        // $pdf->SetXY(35, 208);
        // $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(17, 212);
        $pdf->Cell(0, 3.5, 'Nama ', '0', 0, 'L');
        $pdf->SetXY(35, 212);
        $pdf->Cell(0, 3.5, ': ' . $this->sppb->pejabat->name, '0', 0, 'L');
        $pdf->SetXY(17, 216);
        $pdf->Cell(0, 3.5, 'NIP ', '0', 0, 'L');
        $pdf->SetXY(35, 216);
        $pdf->Cell(0, 3.5, ': ' . $this->sppb->pejabat->nip, '0', 0, 'L');

        $pdf->SetXY(98, 190);
        $pdf->Cell(0, 3.5, 'Pejabat yang melaksanakan pengeluaran barang', '0', 0, 'L');
        $pdf->SetXY(98, 208);
        // $pdf->Cell(0, 3.5, 'Tanda tangan ', '0', 0, 'L');
        // $pdf->SetXY(116, 208);
        // $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(98, 212);
        $pdf->Cell(0, 3.5, 'Nama ', '0', 0, 'L');
        $pdf->SetXY(116, 212);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');
        $pdf->SetXY(98, 216);
        $pdf->Cell(0, 3.5, 'NIP ', '0', 0, 'L');
        $pdf->SetXY(116, 216);
        $pdf->Cell(0, 3.5, ':', '0', 0, 'L');

        // $pdf->Rect(15,233,180,0.1);
        $pdf->SetXY(17, 236);
        $pdf->Line(17, 235, 210-17, 235);
        $pdf->Cell(0, 3.5, 'Lembar 1 untuk Importir / Pemberitahu ', '0', 0, 'L');
        $pdf->SetXY(17, 240);
        $pdf->Cell(0, 3.5, 'Lembar 2 untuk DJBC ', '0', 0, 'L');
    }
}
