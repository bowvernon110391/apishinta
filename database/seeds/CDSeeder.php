<?php

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

        $i = 0;
        while($i++ < $jumlah) {
            // create a passenger
            $p = new App\CD(); 
            $p->no_dok          = $faker->randomNumber(4);
            $p->no_hp           = $faker->e164PhoneNumber;
            $p->tgl_dok         = $faker->date('Y-m-d');
            $p->npwp            = $faker->numerify("###############");
            $p->penumpang_id    = $faker->numberBetween(1,30);
            $p->nib             = $faker->numerify("#############");
            $p->alamat          = $faker->address;
            $p->lokasi_id       = $faker->numberBetween(1,3);
            $p->tgl_kedatangan  = $faker->date('Y-m-d');
            $p->no_flight       = $faker->bothify("?? ####");
            $p->jml_anggota_keluarga    = $faker->numberBetween(0, 5);
            $p->jml_bagasi_dibawa       = $faker->numberBetween(1, 5);
            $p->jml_bagasi_tdk_dibawa = $faker->numberBetween(0, 5);

            $p->save();
            //flag
            $flagCount = random_int(0,9);

            $h = 0;

            while($h++ < $flagCount){
                $df = App\DeclareFlag::find(random_int(1,6));
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

                while($g++ < $kategoriCount){
                    $kat = App\Kategori::find(random_int(1,3));
                    $d->kategoris()->save($kat);
                }

                // isi data sekunder
                $k = 0;
                $dataSekunderCount = random_int(0, 3);

                while ($k++ < $dataSekunderCount) {
                    // create data sekunder
                    $ds = new App\DetailSekunder;
                    $ds->jenis_detail_sekunder_id = $faker->numberBetween(1,4);
                    $ds->data = $faker->sentence(random_int(2,12));

                    $d->detailSekunders()->save($ds);
                }
            }

            
        }
    }
}
