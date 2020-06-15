<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstruksiPemeriksaansTable extends Migration
{
    use MigrationTraitDokumen;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruksi_pemeriksaans', function (Blueprint $table) {
            $table->bigIncrements('id');

            // common doc cols
            $this->addDokumenColumns($table);

            // where this attaches to
            $table->morphs('instructable');
            
            // other data?
            $table->string('nama_issuer');
            $table->string('nip_issuer');

            // just flat cols
            $table->string('nama_importir');
            $table->string('npwp_importir');

            $table->string('nama_kuasa')->nullable()->default(null);
            $table->string('identitas_kuasa')->nullable()->default(null);

            $table->string('jumlah_periksa');
            $table->enum('ajukan_contoh',['Y','T'])->default('T');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instruksi_pemeriksaans');
    }
}
