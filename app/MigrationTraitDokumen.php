<?php
namespace App;

use Illuminate\Database\Schema\Blueprint;

trait MigrationTraitDokumen {

    // when creating
    public function addDokumenColumns(Blueprint $table) {
        // add all necessary dokumen marker
        $table->string('kode_kantor', 8)->index()->default('050100');   // default to 050100 (soetta)
        // add nomor lengkap
        $table->string('nomor_lengkap_dok', 64)->default('')->index();  // default to empty
    }
}