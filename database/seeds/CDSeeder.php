<?php

use App\Airline;
use App\HsCode;
use App\Kurs;
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

            // associate with random pelabuhan
            $p->pelabuhanAsal()->associate(App\Pelabuhan::inRandomOrder()->first());
            // $p->pelabuhanTujuan()->associate(App\Pelabuhan::inRandomOrder()->first());
            $p->kd_pelabuhan_tujuan = 'IDCGK';

            // get fake timestamp between 2 years ago and now
            $ts = $faker->dateTimeBetween('-2 years')->getTimestamp();
            
            $p->no_hp           = $faker->e164PhoneNumber;
            $p->tgl_dok         = date('Y-m-d', $ts);       // use the faker generated timestamp
            $p->npwp            = $faker->numerify("###############");
            // $p->penumpang_id    = App\Penumpang::inRandomOrder()->first()->id;  // $faker->numberBetween(1,30);
            $p->nib             = $faker->numerify("#############");
            $p->alamat          = $faker->address;
            // $p->lokasi_id       = $faker->numberBetween(1,3);
            $p->ndpbm()->associate(
                Kurs::kode('USD')->inRandomOrder()->first()
            );

            // generate valid sequence using helper
            /* $p->no_dok          = getSequence(
                'CD/' . $p->lokasi->nama . '/SH',  // grab its name
                (int)date('Y', strtotime($p->tgl_dok))      // grab the year of tgl_dok
            ); */

            $p->tgl_kedatangan  = $faker->date('Y-m-d');

            // associate with random airline?
            $a = Airline::inRandomOrder()->first();
            $p->no_flight       = $a->kode . ' ' . strtoupper($faker->bothify("###"));
            $p->airline()->associate($a);

            $p->jml_anggota_keluarga    = $faker->numberBetween(0, 5);
            $p->jml_bagasi_dibawa       = $faker->numberBetween(1, 5);
            $p->jml_bagasi_tdk_dibawa = $faker->numberBetween(0, 5);
            $p->pembebasan  = $faker->randomElement([0, 500, 1000]);

            $p->koli = random_int(1, 4);

            $p->setNomorDokumen();
            $p->save();
            // $p->appendStatus('CREATED');

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
            $detailCount = random_int(1, 4);

            $j = 0;

            // for nilai barang
            $kursRef = App\Kurs::inRandomOrder()->first();
            $pejabat = App\SSOUserCache::inRandomOrder()->first();

            while ($j++ < $detailCount) {
                // spawn a new Detail Barang
                $d = new App\DetailBarang([
                    'uraian' => $faker->sentence(random_int(1, 6)),
                    'jumlah_kemasan' => $faker->numberBetween(1, 20),
                    'jenis_kemasan' => $faker->randomElement(['BX','PX','RO','PK','BG']),
                    'fob'         => $faker->randomFloat(4, 500, 980),
                    'freight'     => $faker->randomFloat(4, 0, 200),
                    'insurance'   => $faker->randomFloat(4, 0, 100),
                    'brutto'      => $faker->randomFloat(4, 0.5, 25)
                ]);

                $d->kurs()->associate($kursRef);
                $d->hs()->associate(App\HsCode::usable()->inRandomOrder()->first());

                $p->detailBarang()->save($d);

                // isi data kategori
                $g = 0;
                $kategoriCount = random_int(0,2);
                $kategoriDefs = App\Kategori::inRandomOrder()->get();

                while($g++ < $kategoriCount){
                    $d->kategori()->attach($kategoriDefs[$g]);
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

                    $d->detailSekunder()->save($ds);
                }

                // if there was pejabat, store it as penetapan too
                if ($pejabat && !$d->is_penetapan) {
                    
                    $penetapan = new App\Penetapan();
                    $penetapan->data()->associate($d);
                    $penetapan->pejabat()->associate($pejabat);

                    $penetapan->save();
                }
            }

            while(false /* $j++ < $detailCount */) {
                $d = new App\DetailCD();
                // fill detail data
                $d->uraian      = $faker->sentence(random_int(1, 6));
                $d->jumlah_satuan   = $faker->numberBetween(1, 100);
                $d->jumlah_kemasan  = $faker->numberBetween(1, 20);
                $d->jenis_satuan    = $faker->randomElement(['PCE','KGM','EA']);
                $d->jenis_kemasan   = $faker->randomElement(['BX','PX','RO','PK']);

                // $d->hs_code         = $faker->numerify("########");
                $d->hs()->associate(HsCode::usable()->inRandomOrder()->first());

                $d->fob         = $faker->randomFloat(4, 500, 980);
                $d->freight     = $faker->randomFloat(4, 0, 200);
                $d->insurance   = $faker->randomFloat(4, 0, 100);
                $d->brutto      = $faker->randomFloat(4, 0.5, 25);
                $d->netto       = $faker->randomFloat(4, 0, $d->brutto-0.25);

                // kurs requires special attention
                $kurs = App\Kurs::inRandomOrder()->first();

                // $d->kode_valuta = $kurs->kode_valas; //$faker->randomElement(['USD','AUD','SGD','INR','MYR']);
                // $d->nilai_valuta    = $kurs->kurs_idr; //$faker->randomFloat(4, 100.0, 40000.0);
                $d->kurs()->associate($kursRef);


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
