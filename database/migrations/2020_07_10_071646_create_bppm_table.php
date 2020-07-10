<?php

use App\MigrationTraitDokumen;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBppmTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bppm', function (Blueprint $table) {
            $table->bigIncrements('id');

            $this->addDokumenColumns($table);

            $table->morphs('payable');

            $table->unsignedInteger('pejabat_id');

            $table->timestamps();

            // foreign keys
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
        Schema::dropIfExists('bppm');
    }
}
