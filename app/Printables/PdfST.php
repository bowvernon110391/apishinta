<?php
namespace App\Printables;

use App\ST;
use Fpdf\Fpdf;

class PdfST extends Fpdf {
    protected $st = null;

    // constructor follow usual style
    public function __construct(ST $s, $kota_ttd = null, $tgl_ttd = null)
    {
        $this->st = $s;

        $this->kota_ttd = $kota_ttd ?? "CENGKARENG";
        $this->tgl_ttd = $tgl_ttd ?? date('Y-m-d');

        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true, 14);
    }

    public function generateFirstpage() {
        // throw error if there's nothing here
        if (!$this->st) {
            return new \Exception("st data not provided!");
        }

        // ========================================================================
        // data surat
        $s  = $this->st;

        $no_lengkap     = $s->nomor_lengkap;    //'200312/st/2F/SH/2020';
        $tgl_surat      = formatTanggal($s->tgl_dok);

        $nama   = $s->cd->penumpang->nama;
        $alamat = str_replace("\n", " ", $s->cd->alamat);  //"GG. MASJID NO 50 A, RT 02/RW 02, KEL. KENANGA, KEC. CIPONDOH, KOTA TANGERANG, BANTEN";
        $no_paspor  = $s->cd->penumpang->no_paspor; //"290103-2323-22132";
        $phone  = $s->cd->penumpang->phone;
        $email  = $s->cd->penumpang->email;
        $kebangsaan = $s->cd->penumpang->negara->uraian; //"INDONESIA";
        $no_flight  = "{$s->cd->no_flight} ({$s->cd->airline->uraian})";
        $negara_asal    = $s->negara->uraian;

        $total_brutto   = $s->cd->detailBarang->reduce(function($acc, $e) {
            return $acc + $e->brutto;
        }, 0.0);
        $total_brutto = number_format($total_brutto, 2);

        $summary_jumlah = "{$s->cd->koli} Koli / {$total_brutto} Kg";

        $uraian_summary = $s->cd->detailBarang->map(function ($e) { return $e->uraian; })->toArray(); /* [
            "1. Sepeda Brompton",
            "2. Tas Louis Vutton BR323CI Gold Series"
        ]; */

        $keterangan = $s->keterangan[0]->keterangan ?? "-"; //"DITUNDA PENGELUARANNYA DIKARENAKAN YBS TIDAK DAPAT MEMENUHI KEWAJIBAN PABEAN ATAS BARANG BAWAANNYA";

        $kota_ttd   = $this->kota_ttd;
        $tgl_ttd    = formatTanggal($this->tgl_ttd); // "01 MARET 2020";

        $nama_pejabat   = $s->pejabat->name;
        $nip_pejabat    = $s->pejabat->nip;
        // ========================================================================


        // $p = new Fpdf('P', 'mm', 'A4');
        // $p->SetAutoPageBreak(true);
        $p  = $this;

        $p->SetMargins(12, 12);
        $p->AddPage();
        $p->Rect(10, 10, 210-20, 297-20);

        // set font
        $p->SetFont('Arial', '', 8);

        $currX  = $p->GetX();
        $currY  = $p->GetY();

        // $p->SetXY($currX + 2, $currY + 2);
        $p->Cell(0, 4, "KEMENTERIAN KEUANGAN REPUBLIK INDONESIA", 0, 2);
        $p->Cell(0, 4, "DIREKTORAT JENDERAL BEA DAN CUKAI", 0, 2);
        $p->Cell(0, 4, "KANTOR PELAYANAN UTAMA BEA DAN CUKAI TIPE C SOEKARNO HATTA", 0, 2);
        $p->Ln(8);

        // Kop surat
        $p->SetFont('Arial', 'BU', 8);
        $p->Cell(0, 4, 'TANDA BUKTI PENAHANAN/PENITIPAN', 0, 1, 'C');
        $p->SetFont('Arial', 'BI', 8);
        $p->Cell(0, 4, 'RECEIPT OF DETENTION/DEPOSIT', 0, 1, 'C');

        // no + tgl surat
        $p->SetFont('Arial', '', 8);
        $p->Cell(0, 4, "No. : {$no_lengkap}  Tanggal : {$tgl_surat}", 0, 1, 'C');

        $p->Ln();

        $tabPos = $currX + 42.5;

        // Nama
        $p->Cell($p->GetStringWidth("Nama/"), 4, "Nama/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Name');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $nama, 0, 1);

        $p->Ln(1);

        // Telephone
        $p->Cell($p->GetStringWidth("Nomor Telepon/"), 4, "Nomor Telepon/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Phone');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $phone, 0, 1);

        $p->Ln(1);

        // email
        $p->Cell($p->GetStringWidth("E-mail"), 4, "E-mail");
        // $p->SetFont('Arial', 'I');
        // $p->Cell(0, 4, 'Phone');
        // $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $email, 0, 1);

        $p->Ln(1);

        // Alamat
        $p->Cell($p->GetStringWidth("Alamat/"), 4, "Alamat/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Address');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        // $p->Cell(0, 4, $alamat, 0, 1);
        $p->MultiCell(0, 4, $alamat, 0, 'J');

        $p->Ln(1);

        // Paspor
        $p->Cell($p->GetStringWidth("Paspor/"), 4, "Paspor/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Passport No.');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $no_paspor, 0, 1);

        $p->Ln(1);

        // Kebangsaan
        $p->Cell($p->GetStringWidth("Kebangsaan/"), 4, "Kebangsaan/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Nationality');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $kebangsaan, 0, 1);

        $p->Ln(1);

        // No flight
        $p->SetFont('Arial', 'I');
        $p->Cell($p->GetStringWidth("Flight / "), 4, "Flight / ");
        $p->Cell(0, 4, 'Voyage No.');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $no_flight, 0, 1);
        $p->Ln(1);

        // Asal
        $p->Cell($p->GetStringWidth("Asal/"), 4, "Asal/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Country of Origin');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $negara_asal, 0, 1);

        $p->Ln(1);

        // Jumlah
        $p->Cell($p->GetStringWidth("Jumlah/"), 4, "Jumlah/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Quantity');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        $p->Cell(0, 4, $summary_jumlah, 0, 1);

        $p->Ln(1);

        // Uraian Barang
        $p->Cell($p->GetStringWidth("Uraian Barang/"), 4, "Uraian Barang/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Description');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        // $p->Cell(0, 4, $summary_jumlah, 0, 1);

        $nomor = 1;
        foreach ($uraian_summary as $uraian) {
            $p->Cell(0, 4, "{$nomor}. " . strtoupper($uraian), 0, 2);
            ++$nomor;
        }

        $p->Ln(1);

        // Keterangan
        $p->Cell($p->GetStringWidth("Keterangan/"), 4, "Keterangan/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'Other Detail');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(5, 4, ":");
        // $p->Cell(0, 4, $keterangan, 0, 1);
        $p->MultiCell(0, 4, $keterangan);

        $p->Ln();

        // PENGHITUNGAN BEA MASUK DAN PAJAK IMPUR (JIKA DIPERLUKAN)
        $p->SetFont('Arial', 'U', 8);
        $p->Cell(0, 4, 'PENGHITUNGAN BEA MASUK DAN PAJAK IMPOR (JIKA DIPERLUKAN)', 0, 1);
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'IMPORT DUTY AND TAX CALCULATION (IF NECESSARY)');

        $p->Ln(8);
        // $p->Ln();

        // update tabpos
        $tabPos = $currX + 32.5;

        $startX = $p->GetX();
        $startY = $p->GetY();

        // Nilai pabean
        $p->Cell($p->GetStringWidth("NILAI PABEAN/"), 4, "NILAI PABEAN/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'CUSTOMS VALUE:', 0, 1);
        $p->SetFont('Arial');

        // Nilai Kurs/Curr.
        $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'CURR.');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ":");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // FOB
        // $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        // $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'FOB');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ":");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // INSURANCE
        // $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        // $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'INSURANCE');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ":");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // FREIGHT
        // $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        // $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'FREIGHT');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ":");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // CIF
        // $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        // $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'CIF');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ":");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // draw some line
        $currY  = $p->GetY();
        $p->Line($tabPos, $currY, $tabPos + 50, $currY);
        $p->Ln(1);

        // NILAI PABEAN
        // CIF
        // $p->Cell($p->GetStringWidth("NILAI KURS/"), 4, "NILAI KURS/");
        // $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'NILAI PABEAN');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        // $p->Cell(2.5, 4, "Rp. ");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // another column
        $p->SetXY(108, $startY);
        $tabPos = 108 + 39;

        // PUNGUTAN NEGARA
        $p->Cell($p->GetStringWidth("PUNGUTAN NEGARA/"), 4, "PUNGUTAN NEGARA/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'DUTY AND TAX:', 0, 1);
        $p->SetFont('Arial');

        // BEA MASUK
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("BM/"), 4, "BM/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'IMPORT DUTY');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // DENDA
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("DENDA ADM/"), 4, "DENDA ADM/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'PENALTY');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // PPN
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("PPN/"), 4, "PPN/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'VAT');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // PPnBM
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("PPnBM/"), 4, "PPnBM/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'LUXURY TAX');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // PPh
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("PPh Ps. 21/"), 4, "PPh Ps. 21/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'INCOME TAX');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');

        // draw line
        $currY  = $p->GetY();
        $p->Line($tabPos, $currY, $tabPos+50, $currY);
        $p->Ln(1);

        // TOTAL
        // PPh
        $p->SetX(108);
        $p->Cell($p->GetStringWidth("TOTAL BM PDRI/"), 4, "TOTAL BM PDRI/");
        $p->SetFont('Arial', 'I');
        $p->Cell(0, 4, 'TAX');
        $p->SetFont('Arial');

        $p->SetX($tabPos);
        $p->Cell(10, 4, ": Rp.");
        $p->Cell(30, 4, "-", 0, 1, 'C');


        // Kota TTD
        $p->Ln(8);

        $currX  = 110;
        $currY  = $p->GetY();

        $p->SetXY($currX, $currY);
        $p->Cell(0, 4, "{$kota_ttd} , {$tgl_ttd}", 0, 1);
        $p->Ln();


        $currX  = 35;
        $currY  = $p->GetY();

        // Pemilik Barang
        $p->SetXY($currX, $currY);
        $p->SetFont('Arial', '');
        $p->Cell(36, 4, "Pemilik Barang/Kuasa      ", 0, 2);

        $p->Line($currX + 1, $p->GetY(), $currX + 35, $p->GetY());

        $p->SetFont('Arial', 'I');
        $p->Cell(36, 4, "Goods Owner /On Behalf", 0, 2);
        $p->Ln(20);

        $p->SetXY($currX, $p->GetY());
        $p->SetFont('Arial', '');
        $p->Cell(36, 4, "( {$nama} )", 0, 1, 'C');

        // Pejabat bea dan cukai
        $currX  = 110;
        $p->SetXY($currX, $currY);
        $p->SetFont('Arial', '');
        $p->Cell(36, 4, "Pejabat Bea dan Cukai      ", 0, 2);

        $p->Line($currX + 1, $p->GetY(), $currX + 35, $p->GetY());

        $p->SetFont('Arial', 'I');
        $p->Cell(36, 4, "Customs Officer", 0, 2);
        $p->Ln(20);

        $p->SetXY($currX, $p->GetY());
        $p->SetFont('Arial', '');
        $p->Cell(36, 4, "( {$nama_pejabat} )", 0, 2, 'C');
        $p->Cell(36, 4, "NIP {$nip_pejabat}", 0, 1, 'C');

        $p->Ln(8);

        // Perhatian
        $p->Cell(15, 4, "Perhatian");
        $p->Cell(2.5, 4, ":");
        $p->Cell(4, 4, "a.");
        $p->MultiCell(0, 4, "Barang ditimbun di Tempat Penimbunan Sementara atau tempat lain yang diperlakukan sama dengan Tempat Penimbunan Sementara.");

        $p->SetX($p->GetX() + 17.5);
        $p->Cell(4, 4, "b.");
        $p->MultiCell(0, 4, "Apabila dalam 30 (tiga puluh) hari sejak barang ditimbun di Tempat Penimbunan Sementara barang tidak diselesaikan kewajiban pabeannya, barang akan dinyatakan sebagai Barang Tidak Dikuasai.");

        // garis
        $p->Ln(1);
        $p->Line(12, $p->GetY(), 210-12, $p->GetY());
        $p->Ln(1);

        // Perhatian (inggris)
        $p->SetFont('Arial', 'I');
        $p->Cell(15, 4, "Notice");
        $p->SetFont('Arial');
        $p->Cell(2.5, 4, ":");
        $p->SetFont('Arial', 'I');
        $p->Cell(4, 4, "a.");
        $p->MultiCell(0, 4, "Goods will be stored in Temporary Storage Facility or in other place or storage considered as Temporary Storage Facility.");

        $p->SetX($p->GetX() + 17.5);
        $p->Cell(4, 4, "b.");
        $p->MultiCell(0, 4, "Should you not claim the goods within 30 (thirty) days since storage, they will be stated as Unclaimed Goods by default afterwards.");

        // Peruntukan lembar
        $p->Ln(18);
        $p->SetFont('Arial');

        $p->Cell(0, 4, "Peruntukkan lembar:", 0, 1);
        $p->Cell(0, 4, "1. Penumpang/Awak Sarana Pengangkut;", 0, 1);
        $p->Cell(0, 4, "2. Ditempel pada barang;", 0, 1);
        $p->Cell(0, 4, "3. Pejabat Bea Cukai/Arsip.");

    }
}