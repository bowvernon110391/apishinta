<?php

use Illuminate\Database\Seeder;

class SSPCPSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        $jumlah= 15;

        echo "generating about {$jumlah} SSPCP(s)...\n";

        $a = 0;
        $created = 0;

        while($a++ < $jumlah){
            $cd = App\CD::inRandomOrder()->first();
            $sspcp = $cd->sspcp;

            // don't seed a new one if one already exist
            // or cd is locked
            if ($sspcp || $cd->is_locked) {
                continue;
            } else {
                $created++;
                $sspcp = new App\SSPCP;
            }
            // associate with lokasi
            $sspcp->lokasi()->associate(App\Lokasi::inRandomOrder()->first());

            $sspcp->no_dok = getSequence('SSPCP/'.$sspcp->lokasi->nama.'/SH', date('Y'));
            $sspcp->tgl_dok = date('Y-m-d');
            // $sspcp->lokasi_id = App\Lokasi::inRandomOrder()->first()->id;
            $sspcp->total_fob = $cd->details()->sum('fob');
            $sspcp->total_freight = $cd->details()->sum('freight');
            $sspcp->total_insurance = $cd->details()->sum('insurance');
            $sspcp->total_cif = $sspcp->total_fob + $sspcp->total_freight + $sspcp->total_insurance;
            $sspcp->nilai_valuta = $faker->randomFloat(NULL,92.82, 14067.999);
            $sspcp->kode_valuta = $faker->randomElement(['JPG', 'USD', 'KRW', 'GBP', 'INR']);
            $sspcp->total_nilai_pabean = $sspcp->total_cif * $sspcp->nilai_valuta;
            $sspcp->pembebasan =  $faker->randomElement([0,500,1000]);
            $sspcp->keterangan =  $faker->sentence(10);
            $sspcp->pemilik_barang =  $faker->name();
            $sspcp->ground_handler =  $faker->name();
            $sspcp->pejabat_id =  $faker->numberBetween(1,10);
            

            $cd->sspcp()->save($sspcp);

            foreach ($cd->details as $cdd) {

                $det = new App\DetailSSPCP([
                    'cd_detail_id'  => $cdd->id,
                    'fob'  => $cdd->fob,
                    'freight'  => $cdd->freight,
                    'insurance'  => $cdd->insurance,
                    'cif'  => $cdd->fob + $cdd->freight + $cdd->insurance,
                    'nilai_pabean'  => 0,
                    'pembebasan'  => 0,
                    'trf_bm'  => 0.1,
                    'trf_ppn'  => 0.1,
                    'trf_ppnbm'  => 0,
                    'trf_pph'  => $faker->randomElement([0.025, 0.075, 0.15]),
                    'bm'  => $faker->randomFloat(4,100,1000) * 1000,
                    'ppn'  =>  $faker->randomFloat(4,100,1000) * 1000,
                    'ppnbm'  =>  $faker->randomFloat(4,100,1000) * 1000,
                    'pph'  =>  $faker->randomFloat(4,100,1000) * 1000,
                    'denda'=>  $faker->randomFloat(4,100,1000) * 1000,
                    'keterangan' => $faker->sentence(10),
                    'kode_valuta'  => $faker->randomElement(['JPG', 'USD', 'KRW', 'GBP', 'INR']),
                    'hs_code'  => $faker->numerify("########"),
                    'nilai_valuta' => $faker->randomFloat(NULL,92.82, 14067.999),
                    'brutto' => $faker->randomFloat(5, 500),
                    'netto' => $faker->randomFloat(5, 500),
                ]);

                // $cd->details()[$b]->save($det);
                
                $sspcp->details()->save($det);
            }


            $sspcp->total_bm =  ceil($sspcp->details()->sum('bm'));
            $sspcp->total_ppn =   ceil($sspcp->details()->sum('ppn'));
            $sspcp->total_ppnbm =   ceil($sspcp->details()->sum('ppnbm'));
            $sspcp->total_pph =   ceil($sspcp->details()->sum('pph'));
            $sspcp->total_denda =   ceil($sspcp->details()->sum('denda'));

            $sspcp->push();

            // lock sspcp also locks cd document
            $sspcp->lock();

        }

        echo "SSPCP data seeded with {$created} data.\n";

    }
}
