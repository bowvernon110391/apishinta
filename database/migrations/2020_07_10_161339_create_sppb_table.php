<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSppbTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sppb', function (Blueprint $table) {
            $table->bigIncrements('id');

            $this->addDokumenColumns($table);

            // morphs
            $table->morphs('gateable'); // what's being gateable
            $table->morphs('lokasi');   // lokasi can be TPS or Lokasi
            $table->unsignedInteger('pejabat_id');  // who did this?

            $table->timestamps();

            // foreign
            $table->foreign('pejabat_id')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sppb');
    }
}
