<?php

use App\Lokasi;
use Illuminate\Database\Seeder;

class CDSeeder extends Seeder
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

        echo "generating random {$jumlah} Custom Declaration(s)...\n";

        $i = 0;
        while($i++ < $jumlah) {
            // create a CD
            $p = new App\CD(); 

            // associate it with random penumpang
            $p->penumpang()->associate(App\Penumpang::inRandomOrder()->first());
            // associate it with random lokasi
            $p->lokasi()->associate(App\Lokasi::inRandomOrder()->first());

            // get fake timestamp between 2 years ago and now
            $ts = $faker->dateTimeBetween('-2 years')->getTimestamp();
            
            $p->no_hp           = $faker->e164PhoneNumber;
            $p->tgl_dok         = date('Y-m-d', $ts);       // use the faker generated timestamp
            $p->npwp            = $faker->numerify("###############");
            // $p->penumpang_id    = App\Penumpang::inRandomOrder()->first()->id;  // $faker->numberBetween(1,30);
            $p->nib             = $faker->numerify("#############");
            $p->alamat          = $faker->address;
            // $p->lokasi_id       = $faker->numberBetween(1,3);

            // generate valid sequence using helper
            $p->no_dok          = getSequence(
                'CD/' . $p->lokasi->nama . '/SH',  // grab its name
                (int)date('Y', strtotime($p->tgl_dok))      // grab the year of tgl_dok
            );

            $p->tgl_kedatangan  = $faker->date('Y-m-d');
            $p->no_flight       = strtoupper($faker->bothify("?? ####"));
            $p->jml_anggota_keluarga    = $faker->numberBetween(0, 5);
            $p->jml_bagasi_dibawa       = $faker->numberBetween(1, 5);
            $p->jml_bagasi_tdk_dibawa = $faker->numberBetween(0, 5);

            $p->save();

            // isi data flag
            $flagCount = random_int(0,6);

            $h = 0;

            // bikin preset flag
            // declare_flag_id ada 6 [1..6]
            // diacak dlu
            $presetFlagId =  $faker->shuffle([1,2,3,4,5,6]);

            // buang beberapa sekitar ~0..6 data dibuang dari depan
            array_splice($presetFlagId, 0, random_int(0,6) );

            // baru tambahkan jadi flag
            foreach ($presetFlagId as $flagId) {
                $df = App\DeclareFlag::find( $flagId );
                $p->declareFlags()->save($df);
            }

            // create teh details
            $detailCount = random_int(0, 4);

            $j = 0;
            while($j++ < $detailCount) {
                $d = new App\DetailCD();
                // fill detail data
                $d->uraian      = $faker->sentence(random_int(1, 6));
                $d->jumlah_satuan   = $faker->numberBetween(1, 100);
                $d->jumlah_kemasan  = $faker->numberBetween(1, 20);
                $d->jenis_satuan    = $faker->randomElement(['PCE','KGM','EA']);
                $d->jenis_kemasan   = $faker->randomElement(['BX','PX','RO','PK']);
                $d->hs_code         = $faker->numerify("########");
                $d->fob         = $faker->randomFloat(4);
                $d->freight     = $faker->randomFloat(4);
                $d->insurance   = $faker->randomFloat(4);
                $d->brutto      = $faker->randomFloat(4, 0.5);
                $d->netto       = $faker->randomFloat(4, 0, $d->brutto-0.25);
                $d->kode_valuta = $faker->randomElement(['USD','AUD','SGD','INR','MYR']);
                $d->nilai_valuta    = $faker->randomFloat(4, 100.0, 40000.0);


                $p->details()->save($d);

                // isi data kategori
                $g = 0;
                $kategoriCount = random_int(0,2);
                $kategoriDefs = App\Kategori::inRandomOrder()->get();

                while($g++ < $kategoriCount){
                    $d->kategoris()->attach($kategoriDefs[$g]);
                }

                // isi data sekunder
                $k = 0;
                $dataSekunderCount = random_int(0, 3);

                while ($k++ < $dataSekunderCount) {
                    // create data sekunder
                    $ds = new App\DetailSekunder;
                    // $ds->jenis_detail_sekunder_id = $faker->numberBetween(1,4);
                    $ds->referensiJenisDetailSekunder()->associate(App\ReferensiJenisDetailSekunder::inRandomOrder()->first());
                    $ds->data = $faker->sentence(random_int(2,12));

                    $d->detailSekunders()->save($ds);
                }
            }

            
        }

        echo "Custom Declarations data seeded.\n";
    }
}
