<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCancellableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancellable', function (Blueprint $table) {
            $table->bigIncrements('id');

            // id surat pembatalan
            $table->unsignedBigInteger('pembatalan_id');

            // morphs
            $table->morphs('cancellable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cancellable');
    }
}
