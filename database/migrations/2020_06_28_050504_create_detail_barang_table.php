<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_barang', function (Blueprint $table) {
            $table->bigIncrements('id');

            // first, make it polymorphic
            $table->nullableMorphs('header');   // header doc for this detail

            // uraian is important
            $table->text('uraian');

            // jumlah n jenis kemasan is a MUST!
            $table->decimal('jumlah_kemasan');
            $table->string('jenis_kemasan', 8);

            // jumlah n jenis satuan is OPTIONAL, so NULLABLE
            $table->decimal('jumlah_satuan')->nullable()->default(null);
            $table->string('jenis_satuan', 8)->nullable()->default(null);

            // hs is a must, but make it optional (some don't need em)
            $table->unsignedInteger('hs_id')->nullable();

            // CIF uses decimal, as brutto and netto
            $table->decimal('fob', 18, 4);
            $table->decimal('insurance', 18, 4);
            $table->decimal('freight', 18, 4);

            // make netto optional
            $table->decimal('brutto', 8, 4);
            $table->decimal('netto', 8, 4)->nullable()->default(null);

            // kurs is a must
            $table->unsignedInteger('kurs_id');

            $table->timestamps();

            // ====================FOREIGN KEYS===========================================
            $table->foreign('jenis_kemasan', 'fk1_jenis_kemasan')->references('kode')->on('referensi_kemasan');
            $table->foreign('jenis_satuan', 'fk2_jenis_satuan')->references('kode')->on('referensi_satuan');

            $table->foreign('hs_id', 'fk3_hs')->references('id')->on('hs_code');

            $table->foreign('kurs_id', 'fk4_kurs')->references('id')->on('kurs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop foreign key first?
        Schema::dropIfExists('detail_barang');
    }
}
