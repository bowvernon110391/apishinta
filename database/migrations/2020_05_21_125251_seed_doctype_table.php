<?php

use App\BPJ;
use App\CD;
use App\IS;
use App\SPP;
use App\ST;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedDoctypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // fill some generic doctype table here
        DB::table('doctype')->insert([
            // CD
            [
                'class_name'    => CD::class,
                'kode_dok'      => 'BC22',
                'nama'          => 'BC 2.2 (Customs Declaration)',
                'deskripsi'     => 'Dokumen pemberitahuan impor untuk barang penumpang dan awak sarana pengangkut'
            ],
            // SPP
            [
                'class_name'    => SPP::class,
                'kode_dok'      => 'SPP',
                'nama'          => 'Persetujuan Penangguhan Pengeluaran',
                'deskripsi'     => 'Dokumen persetujuan penagguhan pengeluaran barang penumpang dan awak sarana pengangkut'
            ],
            // ST
            [
                'class_name'    => ST::class,
                'kode_dok'      => 'ST',
                'nama'          => 'Tanda Bukti Penahanan/Penitipan',
                'deskripsi'     => 'Dokumen bukti penahanan/penitipan barang penumpang dan awak sarana pengangkut'
            ],
            // IS
            [
                'class_name'    => IS::class,
                'kode_dok'      => 'BC21',
                'nama'          => 'Pemberitahuan Impor Sementara',
                'deskripsi'     => 'Dokumen pemberitahuan impor sementara untuk barang penumpang dan awak sarana pengangkut'
            ],
            // BPJ
            [
                'class_name'    => BPJ::class,
                'kode_dok'      => 'BPJ',
                'nama'          => 'Bukti Penerimaan Jaminan',
                'deskripsi'     => 'Dokumen bukti penerimaan jaminan'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('doctype')->truncate();
    }
}
