<?php

use Illuminate\Database\Seeder;

class ISSeed extends Seeder
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

        echo "generating about {$jumlah} IS(s)...\n";

        $a = 0;
        $created = 0;

        while($a++ < $jumlah){
            $cd = App\CD::inRandomOrder()->first();

            // do not create IS if existing
            // or cd is locked
            $is = $cd->imporSementara;

            if ($is || $cd->is_locked) {
                continue;
            } else {
                $is = new App\IS;
            }
            // increment counter
            $created++;

            // $is->lokasi_id = App\Lokasi::inRandomOrder()->first()->id;
            // associate with lokasi
            $is->lokasi()->associate(App\Lokasi::inRandomOrder()->first());
            
            $is->no_dok = getSequence('IS/'.$is->lokasi->nama.'/SH', date('Y'));
            $is->tgl_dok = date('Y-m-d');
            
            $is->total_fob = $cd->details()->sum('fob');
            $is->total_freight = 0; // $cd->details()->sum('freight');
            $is->total_insurance = 0; //$cd->details()->sum('insurance');
            $is->total_cif = $is->total_fob + $is->total_freight + $is->total_insurance;
            $is->nilai_valuta = $faker->randomFloat(NULL,92.82, 14067.999);
            $is->kode_valuta = $faker->randomElement(['JPY', 'USD', 'KRW', 'GBP', 'INR']);
            $is->total_nilai_pabean = $is->total_cif * $is->nilai_valuta;
            $is->pembebasan =  $faker->randomElement([0,500,1000]);
            $is->keterangan =  $faker->sentence(10);
            $is->pemilik_barang =  $faker->name();
            $is->pejabat_id =  $faker->numberBetween(1,10);
            $is->total_brutto = 0;
            $is->total_netto = 0;
            $is->tgl_jatuh_tempo = date('y-m-d');
            
            $cd->imporSementara()->save($is);

            foreach ($cd->details as $cdd) {

                $det = new App\DetailIS([
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
                
                $is->details()->save($det);
            }


            $is->total_bm =  ceil($is->details()->sum('bm'));
            $is->total_ppn =   ceil($is->details()->sum('ppn'));
            $is->total_ppnbm =   ceil($is->details()->sum('ppnbm'));
            $is->total_pph =   ceil($is->details()->sum('pph'));
            $is->total_denda =   ceil($is->details()->sum('denda'));
            $is->total_brutto =   ceil($is->details()->sum('brutto'));
            $is->total_netto =   ceil($is->details()->sum('netto'));

            $is->push();

            // lock cd and is
            // $cd->lock();
            $is->lock();

        }

        echo "IS data seeded with {$created} data.\n";
    }
}
