<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePibkTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pibk', function (Blueprint $table) {
            $table->increments('id');

            $this->addDokumenColumns($table);

            // source bisa null, polymorphic
            $table->nullableMorphs('source');
            
            // pemberitahu is polymorphic nullable
            $table->nullableMorphs('pemberitahu');
            
            // importir is polymorphic
            $table->morphs('importir');
            $table->string('npwp', 32);
            // alamat? perlu gk y?
            $table->text('alamat');
            // lokasi barang, polymorphic
            $table->morphs('lokasi');

            // kedatangan, dst
            $table->date('tgl_kedatangan')->nullable();
            $table->string('no_flight', 64);
            $table->string('kd_airline', 4);
            $table->string('kd_pelabuhan_asal', 8);
            $table->string('kd_pelabuhan_tujuan', 8);

            // tarif dan kurs
            $table->unsignedInteger('ndpbm_id')->nullable();
            $table->decimal('pph_tarif', 18, 4);

            // koli
            $table->unsignedInteger('koli');

            $table->timestamps();

            // setup foreign keys
            // ===============================
            $table->foreign('kd_pelabuhan_asal')->references('kode')->on('pelabuhan');
            $table->foreign('kd_pelabuhan_tujuan')->references('kode')->on('pelabuhan');
            $table->foreign('ndpbm_id')->references('id')->on('kurs');
            $table->foreign('kd_airline')->references('kode')->on('airline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pibk');
    }
}
