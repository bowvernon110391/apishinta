<?php

use App\ReferensiJenisPungutan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedJenisPungutanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ReferensiJenisPungutan::whereNotNull('id')->delete();

        Schema::table('referensi_jenis_pungutan', function (Blueprint $table) {
            // just seed some basic shiet
            $seeds = [
                ['BK', 'Bea Keluar', '412211'],
                ['BM', 'Bea Masuk', '412111'],
                ['BMAD', 'Bea Masuk Anti Dumping', '412121'],
                ['BMI', 'Bea Masuk Imbalan', '412122'],
                ['BMTP', 'Bea Masuk Tindakan Pengamanan', '412123'],
                ['DA_PAB', 'Denda Administrasi Pabean', '412113'],
                ['PPh', 'PPh Impor', '411123'],
                ['PPN', 'PPN Impor', '411212'],
                ['PPnBM', 'PPnBM Impor', '411222'],
            ];

            foreach ($seeds as $s) {
                ReferensiJenisPungutan::create([
                    'kode' => $s[0],
                    'nama' => $s[1],
                    'kode_akun' => $s[2]
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referensi_jenis_pungutan', function (Blueprint $table) {
            // just truncate em
            
        });
    }
}
