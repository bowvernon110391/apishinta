<?php
namespace App\Printables;

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

    // constructor is private, because it's multi purpose
    protected function __construct() {
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true);
    }

    // extract data from somewhere?
    public function extractDataFromSSPCP($s) {
        $doc = $s->billable;
        if (!$doc) {
            throw new \Exception("Dokumen ini tidak ada parentnya!");
        }

        $this->jenis_penerimaan_negara  = $s->jenis;

        $this->no_identitas     = $s->no_identitas_wajib_bayar; //$cd->penumpang->no_paspor;
        $this->nama_penumpang   = $s->nama_wajib_bayar; //$cd->penumpang->nama;
        $this->alamat           = $s->alamat_wajib_bayar; //$cd->alamat;

        $this->npwp_pt  = $s->npwp_wajib_bayar ?? '-'; //strlen($cd->npwp) > 13 ? $cd->npwp : '-';

        $this->jenis_dokumen_dasar  = $doc->jenis_dokumen_lengkap; //'CUSTOMS DECLARATION (BC 2.2)';
        $this->nomor_dokumen_dasar  = $doc->nomor_lengkap_dok; //$cd->nomor_lengkap;
        $this->tgl_dokumen_dasar    = formatTanggalDMY($doc->tgl_dok);

        // gotta put something into data pungutan
        $this->data_pungutan    = [];

        // 1. BEA MASUK
        if ($s->total_bm > 0.0) {
            $this->data_pungutan[] = [
                'nama_akun' => 'Bea Masuk',
                'kode_akun' => '412111',
                'jumlah_pungutan'   => number_format($s->total_bm, 2),
                'is_pajak'          => false
            ];
        }
        // 2. PPN IMPOR
        if ($s->total_ppn > 0.0) {
            $this->data_pungutan[] = [
                'nama_akun' => 'PPN Impor',
                'kode_akun' => '411212',
                'jumlah_pungutan'   => number_format($s->total_ppn, 2),
                'is_pajak'          => true
            ];
        }
        // 3. PPh IMPOR
        if ($s->total_pph > 0.0) {
            $this->data_pungutan[] = [
                'nama_akun' => 'PPh Pasal 22 Impor',
                'kode_akun' => '411123',
                'jumlah_pungutan'   => number_format($s->total_pph, 2),
                'is_pajak'          => true
            ];
        }
        // 4. PPnBM
        if ($s->total_ppnbm > 0.0) {
            $this->data_pungutan[] = [
                'nama_akun' => 'PPnBM Impor',
                'kode_akun' => '411222',
                'jumlah_pungutan'   => number_format($s->total_ppnbm, 2),
                'is_pajak'          => true
            ];
        }
        // 5. Denda
        if ($s->total_denda > 0.0) {
            $this->data_pungutan[] = [
                'nama_akun' => 'Denda Administrasi Pabean',
                'kode_akun' => '412113',
                'jumlah_pungutan'   => number_format($s->total_denda, 2),
                'is_pajak'          => false
            ];
        }

        $total=$s->total_bm + $s->total_ppn + $s->total_pph + $s->total_ppnbm + $s->total_denda;

        $this->jumlah_pembayaran    = number_format($total);
        $this->jumlah_pembayaran_text = strtoupper(trim(penyebutRupiah($total)));

        $this->no_bppm  = $s->nomor_lengkap_dok; //$this->formatBppmSequence($s->no_dok, $s->tgl_dok, $this->kode_kantor);
        $this->tgl_bppm = formatTanggalDMY($s->tgl_dok);

        $this->nama_pejabat = $s->nama_pejabat;
        $this->nip_pejabat  = $s->nip_pejabat;
    }

    // function to create number of BPPM
    protected function formatBppmSequence($nomor, $sqlDate, $kode_kantor) {
        $monthLetter = [
            '01'    => 'A',
            '02'    => 'B',
            '03'    => 'C',
            '04'    => 'D',
            '05'    => 'E',
            '06'    => 'F',
            '07'    => 'G',
            '08'    => 'H',
            '09'    => 'I',
            '10'    => 'J',
            '11'    => 'K',
            '12'    => 'L',
        ];

        $month  = substr($sqlDate, 5, 2);
        
        $part1  = substr($sqlDate, 2, 2);
        $part2  = $kode_kantor;
        $part3  = $monthLetter[$month];
        $part4  = str_pad($nomor, 7, '0', STR_PAD_LEFT);

        return $part1.$part2.$part3.$part4;
    }

    // make from SSPCP
    static public function createFromSSPCP($s) {
        $pdf = new PdfBPPM();
        $pdf->extractDataFromSSPCP($s);
        $pdf->generateFirstpage();

        return $pdf;
    }

    // bikin dari SSPCP
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

        foreach ($this->data_pungutan as $d) {
            $p->SetX($p->GetX() + 5);

            if ($d['is_pajak']) {
                // tax must print no_identitas
                $p->Cell(40, 6, $d['nama_akun'], 1, 0);
                // npwp pt
                $p->Cell(45, 6, "NPWP : " . $this->npwp_pt, 1, 0);
            } else {
                // non tax goes here
                $p->Cell(85, 6, $d['nama_akun'], 1, 0);
            }

            // kode akun
            $p->Cell(35, 6, $d['kode_akun'], 1, 0, 'C');

            // the real sheit. print some RP sign first
            $p->Text($p->GetX() + 2, $p->GetY() + 4, 'Rp. ');
            $p->Cell(0, 6, $d['jumlah_pungutan'], 'LTB', 1, 'R');
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