<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLhpTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lhp', function (Blueprint $table) {
            $table->bigIncrements('id');

            // common dokumen column (no, no_lengkap_dok, tgl_dok, etc)
            $this->addDokumenColumns($table);

            $table->morphs('inspectable');  // who owns us

            $table->text('isi');
            $table->string('nama_pejabat');
            $table->string('nip_pejabat');
            $table->integer('lokasi_id')->unsigned();

            $table->timestamps();

            // add FOREIGN KEYS
            $table->foreign('lokasi_id')->references('id')->on('lokasi');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lhp');
    }
}
