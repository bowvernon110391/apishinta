<?php

use App\Pembatalan;
use Illuminate\Database\Seeder;

class PembatalanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create faker instance
        $faker = Faker\Factory::create();

        $jml = random_int(4, 8);

        echo "Generating {$jml} Pembatalan\n";

        for ($i = 0; $i < $jml; ++$i) {
            // instantiate pembatalan and create shits
            $p = new Pembatalan();

            $p->nomor_lengkap_dok   = "S-" . str_pad(random_int(2, 9221), 4, "0", STR_PAD_LEFT) . "/KPU.03/2020";
            $p->tgl_dok = date("Y-m-d", time() + random_int(-3450, 3450));

            $p->nip_pejabat = "199103112012101001";
            $p->nama_pejabat= "Tri Mulyadi Wibowo";

            $p->keterangan  = $faker->sentences(5, true);

            $p->save();
        }

        echo "Pembatalans generated.\n";
    }
}
