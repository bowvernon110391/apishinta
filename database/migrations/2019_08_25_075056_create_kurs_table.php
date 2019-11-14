<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kurs', function (Blueprint $table) {
            // it's a rolling table, but not too frequently updated (54 times a year, so use unsigned int)
            $table->increments('id');
            $table->string('kode_valas', 8)->index();
            $table->decimal('kurs_idr', 18, 4);
            $table->enum('jenis', ['KURS_PAJAK', 'KURS_BI'])->default('KURS_PAJAK');
            $table->date('tanggal_awal');   // valid dari
            $table->date('tanggal_akhir')->default('9999-12-31');  // sampai...by default diasumsikan berlaku selamanya
            $table->timestamps();

            // tambahkan composite key untuk {tanggal_awal, tanggal_akhir, kode_valas}
            $table->index([
                'tanggal_awal',
                'tanggal_akhir',
                'kode_valas'
            ]);
        });

        // seed data awal dengan kurs default IDR yang berlaku seumur hidup
        $kursIDR = new App\Kurs;

        $kursIDR->kode_valas    = 'IDR';
        $kursIDR->kurs_idr      = 1.0;
        $kursIDR->jenis         = 'KURS_PAJAK';
        $kursIDR->tanggal_awal  = '1000-01-01';
        // tanggal akhir by default extend beyond infinite (heh)
        $kursIDR->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kurs');
    }
}
