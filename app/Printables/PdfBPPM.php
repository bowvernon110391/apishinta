<?php
namespace App\Printables;

use App\BPPM;
use Fpdf\Fpdf;

class PdfBPPM extends Fpdf {
    // ====================================================================================================================
    protected $nama_kantor    = 'KANTOR PELAYANAN UTAMA BEA DAN CUKAI TIPE C SOEKARNO HATTA';
    protected $kode_kantor    = '050100';

    protected $jenis_penerimaan_negara    = 'IMPOR';

    protected $jenis_identitas    = 'PASPOR';
    protected $no_identitas       = '39823-2131232-XXYA';
    protected $nama_penumpang     = 'Boris Johnson';
    protected $alamat             = 'JL. Jend. Hasanuddin no 53, kav. 36, Jakarta asd asd qwdqwdqw dqw d qwd qwd qw dqw dqw d qw';

    protected $npwp_pt            = '367101254622000';

    protected $jenis_dokumen_dasar    = 'CUSTOMS DECLARATION (BC 2.2)';
    protected $nomor_dokumen_dasar    = '000003/CD/T2F/SH/2020';
    protected $tgl_dokumen_dasar      = '02-03-2020';

    protected $data_pungutan  = [
        [
            'nama_akun' => 'Bea Masuk',
            'kode_akun' => '412111',
            'jumlah_pungutan'   => '1,350,000.00',
            'is_pajak'          => false
        ],
        [
            'nama_akun' => 'PPN Impor',
            'kode_akun' => '411212',
            'jumlah_pungutan'   => '2,050,000.00',
            'is_pajak'          => true
        ],
        [
            'nama_akun' => 'PPh Pasal 22 Impor',
            'kode_akun' => '411123',
            'jumlah_pungutan'   => '4,100,000.00',
            'is_pajak'          => true
        ]

    ];

    protected $jumlah_pembayaran  = '7,500,000.00';
    protected $jumlah_pembayaran_text = "TUJUH JUTA LIMA RATUS RIBU RUPIAH";

    protected $nmkasir = "Bendahara Penerimaan KPU BC Tipe C Soekarno Hatta";
    protected $npwpkasir = "317022183992000";
    protected $alamatkasir = "Area Kargo Bandara Soekarno Hatta";

    protected $no_bppm    = '20050100C0000023';
    protected $tgl_bppm   = '02-03-2020';

    protected $nama_pejabat   = 'Tri Mulyadi Wibowo';
    protected $nip_pejabat    = '199103112012101001';

    // 
    public function __construct(BPPM $bppm) {
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true);

        $this->bppm = $bppm;

        $this->extractData();
    }

    // extract data from somewhere?
    public function extractData() {
        $b = $this->bppm;
        $payer = $b->payable->payer;

        $this->jenis_penerimaan_negara  = $b->payable->jenis_penerimaan;

        $this->no_identitas     = $payer['no_identitas'];
        $this->nama_penumpang   = $payer['nama']; //$cd->penumpang->nama;
        $this->alamat           = $payer['alamat']; //$cd->alamat;
        $this->jenis_identitas  = $payer['jenis_identitas'];

        $this->npwp_pt  = $payer['npwp'];

        $this->jenis_dokumen_dasar  = $b->payable->jenis_dokumen_lengkap; //'CUSTOMS DECLARATION (BC 2.2)';
        $this->nomor_dokumen_dasar  = $b->payable->nomor_lengkap_dok; //$cd->nomor_lengkap;
        $this->tgl_dokumen_dasar    = formatTanggalDMY($b->payable->tgl_dok);

        // gotta put something into data pungutan

        $total=$b->payable->pungutan->reduce(function($acc, $e){
            return $acc + $e->bayar;
        }, 0.0);

        $this->jumlah_pembayaran    = number_format($total);
        $this->jumlah_pembayaran_text = strtoupper(trim(penyebutRupiah($total)));

        $this->no_bppm  = $b->nomor_lengkap_dok; //$this->formatBppmSequence($s->no_dok, $s->tgl_dok, $this->kode_kantor);
        $this->tgl_bppm = formatTanggalDMY($b->tgl_dok);

        $this->nama_pejabat = $b->pejabat->name;
        $this->nip_pejabat  = $b->pejabat->nip;
    }

    // bikin dari BPPM
    public function generateFirstpage() {
        $p  = $this;

        $p->SetMargins(20, 20, 15);

        // add new page
        $p->AddPage();
        $p->SetFont('Arial', '', 8);

        // current pos
        $currX  = $p->GetX();
        $currY  = $p->GetY();

        // KOP SURAT
        // output image

        $p->Image(__DIR__. '/icon-kemenkeu-grayscale.jpg', null, null, 15, 15);

        $p->SetXY($currX + 17.5, $currY);

        $p->SetFont('Arial', '', 9);
        $p->Cell(0, 5, 'KEMENTERIAN KEUANGAN REPUBLIK INDONESIA', 0, 2);

        $p->SetFont('Arial', '', 8);
        $p->Cell(0, 5, 'DIREKTORAT JENDERAL BEA DAN CUKAI', 0, 2);

        $p->Cell(0, 5, 'Kantor     : ' . $this->nama_kantor, 0, 1);

        $p->Ln(1);
        $p->SetLineWidth(0.5);
        $p->Line($p->GetX(), $p->GetY(), $p->GetX() + 175, $p->GetY());
        // $p->Ln(1);

        // Body
        $p->SetFont('Arial', '', 8);

        // Kode kantor
        $p->Cell(0, 6, 'Kode Kantor         : ' . $this->kode_kantor, 0, 1);

        $p->SetLineWidth(0.3);
        $p->Line($p->GetX(), $p->GetY(), $p->GetX() + 175, $p->GetY());

        // BUKTI PENERIMAAN PEMBAYARAN MANUAL (BPPM)
        $p->SetFont('Arial', 'B', 12);
        $p->MultiCell(0, 6, "BUKTI PENERIMAAN PEMBAYARAN MANUAL\n(BPPM)", 0, 'C');

        $p->Line($p->GetX(), $p->GetY(), $p->GetX() + 175, $p->GetY());

        $colon  = " :  ";

        $p->SetFont('Arial', '', 8);
        // A. JENIS PENERIMAAN NEGARA
        $p->Cell(52.5, 6, 'A. JENIS PENERIMAAN NEGARA', 'RB', 0);
        $p->Cell(0, 6, $colon . $this->jenis_penerimaan_negara, 'LB', 1);
        // B. JENIS IDENTITAS
        $p->Cell(52.5, 6, 'B. JENIS IDENTITAS', 'RB', 0);
        $p->Cell(0, 6, $colon . $this->jenis_identitas, 'LB', 1);

        $currX  = $p->GetX();
        $currY  = $p->GetY();

        // B1. NOMOR
        $p->SetX($currX + 5);   $p->Cell(47.5, 6, 'NOMOR', 'LRB', 0);
        $p->Cell(0, 6, $colon . $this->no_identitas, 'LB', 1);
        // B2. NAMA
        $p->SetX($currX + 5);   $p->Cell(47.5, 6, 'NAMA', 'LRB', 0);
        $p->Cell(0, 6, $colon . $this->nama_penumpang, 'LB', 1);
        // B3. ALAMAT (special case)
        $currX  = $p->GetX();
        $currY  = $p->GetY();

        $p->SetX($currX + 5);   $p->Cell(47.5, 6, 'ALAMAT', 0, 0);
        $p->Cell(3, 6, $colon, 0, 0);
        $p->MultiCell(0, 6, $this->alamat, 0, 'L');

        // grab new y
        $rect_h = $p->GetY() - $currY;
        // draw rectangle
        // $p->Rect(25, $currY, 170, $rect_h);
        $p->Line(25, $currY, 25, $currY + $rect_h);
        $p->Line(72.5, $currY, 72.5, $currY + $rect_h);

        // C. DOKUMEN DASAR PEMBAYARAN
        $p->Cell(52.5, 6, 'C. DOKUMEN DASAR PEMBAYARAN', 'TRB', 0);
        $p->Cell(0, 6, $colon . $this->jenis_dokumen_dasar, 'TLB', 1);

        // C1. NOMOR
        $p->SetX($currX + 5);   $p->Cell(47.5, 6, 'NOMOR', 'LRB', 0);
        $p->Cell(72.5, 6, $colon . $this->nomor_dokumen_dasar, 1, 0);

        // C2. Tanggal
        $p->SetX($currX + 125); $p->Cell(0, 6, 'Tanggal : ' . $this->tgl_dokumen_dasar, 'LB', 1);

        // D. PEMBAYARAN PENERIMAAN NEGARA
        $p->Cell(0, 6, 'D. PEMBAYARAN PENERIMAAN NEGARA', 'TB', 1);

        // record currX and y
        $currX  = $p->GetX();
        $currY  = $p->GetY();

        // AKUN, KODE AKUN, JUMLAH PEMBAYARAN
        $p->SetX($currX+5); 
        $p->Cell(85, 6, 'AKUN', 1, 0, 'C');
        $p->Cell(35, 6, 'KODE AKUN', 1, 0, 'C');
        $p->Cell(0, 6, 'JUMLAH PEMBAYARAN', 'TBL', 1, 'C');

        $pungutan = $this->bppm->payable->pungutan;

        foreach ($pungutan as $d) {
            $p->SetX($p->GetX() + 5);

            if ($d->jenisPungutan->kode[0] == 'P') {
                // tax must print no_identitas
                $p->Cell(40, 6, $d->jenisPungutan->nama, 1, 0);
                // npwp pt
                $p->Cell(45, 6, "NPWP : " . $this->npwp_pt, 1, 0);
            } else {
                // non tax goes here
                $p->Cell(85, 6, $d->jenisPungutan->nama, 1, 0);
            }

            // kode akun
            $p->Cell(35, 6, $d->jenisPungutan->kode_akun, 1, 0, 'C');

            // the real sheit. print some RP sign first
            $pungutan_fmt = number_format($d->bayar, 2);
            $p->Text($p->GetX() + 2, $p->GetY() + 4, 'Rp. ');
            $p->Cell(0, 6, $pungutan_fmt, 'LTB', 1, 'R');
            // $p->Ln();
        }

        // E. Jumlah Pembayaran
        $str = 'E. Jumlah Pembayaran Penerimaan Negara   : ';
        $p->Cell($p->GetStringWidth($str), 6, $str, 'TB', 0);
        $p->Cell(0, 6, 'Rp. ' . $this->jumlah_pembayaran, 'TB', 1, 'C');

        // record current xy
        $currX  = $p->GetX();
        $currY  = $p->GetY();

        $p->SetX($currX + 10);
        $p->Cell($p->GetStringWidth($str)-10, 6, 'Dengan Huruf   : ', 0, 0);
        $p->Cell(0, 6, $this->jumlah_pembayaran_text, 0, 1, 'C');

        $lineStartY = $p->GetY();
        $p->Line($p->GetX(), $p->GetY(), $p->GetX() + 175, $p->GetY());

        // penerima
        $p->Cell(20, 4, 'Diterima Oleh', 0, 0);
        $p->Cell(80, 4, $colon . $this->nmkasir, 0, 1);
        // npwp kasir
        $p->Cell(20, 4, 'NPWP', 0, 0);
        $p->Cell(80, 4, $colon . $this->npwpkasir, 0, 1);
        // nama kantor
        $p->Cell(20, 4, 'Nama Kantor', 0, 0);
        $p->Cell(80, 4, $colon . $this->nama_kantor, 0, 1);
        // nomor bppm
        $p->Cell(20, 4, 'Nomor BPPM', 0, 0);
        $p->Cell(80, 4, $colon . $this->no_bppm, 0, 1);
        // tanggal bppm
        $p->Cell(20, 4, 'Tanggal', 0, 0);
        $p->Cell(80, 4, $colon . $this->tgl_bppm, 0, 1);

        $p->Ln(16);

        // nama nip pejabat
        $p->Cell(20, 4, 'Nama', 0, 0);
        $p->Cell(80, 4, $colon . $this->nama_pejabat, 0, 1);

        $p->Cell(20, 4, 'NIP', 0, 0);
        $p->Cell(80, 4, $colon . $this->nip_pejabat, 0, 1);

        // draw line
        $p->Line($p->GetX(), $p->GetY(), $p->GetX() + 175, $p->GetY());

        // draw vertical line?
        $p->Line(145, $lineStartY, 145, $p->GetY());

        // draw another text
        $p->SetFont('Arial', 'I', 8);
        $p->Cell(0, 6, 'cetak 2 lembar: untuk bendahara penerimaan dan untuk pengguna jasa');

    }
}