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

        $jumlah= 30;

        echo "generating about {$jumlah} SSPCP(s)...\n";

        $a = 0;

        while($a++ < $jumlah){
            $cd = App\CD::inRandomOrder()->first();
            $sspcp = $cd->sspcp ?? new App\SSPCP;

            $sspcp->no_dok = getSequence('SSPCP/SH', date('Y'));
            $sspcp->tgl_dok = date('Y-m-d');
            $sspcp->lokasi_id = App\Lokasi::inRandomOrder()->first()->id;
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
            // $sspcp->total_bm =  ceil($sspcp->total_nilai_pabean/1000 * 0.1)*1000;
            // $sspcp->total_ppn =  ceil(($sspcp->total_nilai_pabean + $sspcp->total_bm)/1000 * 0.1)*1000;
            // $sspcp->total_ppn =  ceil(($sspcp->total_nilai_pabean + $sspcp->total_bm)/1000 * 0.1)*1000;

            $cd->sspcp()->save($sspcp);

            $b = 0;
            foreach ($cd->details as $cdd) {
                $cdd = $cd->details[$b];

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
        }

        echo "SSPCP data seeded.\n";

    }
}
