<?php

use Illuminate\Database\Seeder;

class BPJSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // instantiate faker
        $faker = Faker\Factory::create();

        $jumlah = 10;

        echo "generating random {$jumlah} BPJ...\n";

        $i = 0;
        while($i++ < $jumlah) {
            // create a BPJ
            $b = new App\BPJ();

            // associate penumpang
            $b->penumpang()->associate(App\Penumpang::inRandomOrder()->first());
            // associate lokasi
            $b->lokasi()->associate(App\Lokasi::inRandomOrder()->first());

            $ts = $faker->dateTimeBetween('-2 months')->getTimestamp();
            $b->tgl_dok = date('Y-m-d', $ts);
            // $b->no_dok = '';

            $b->jenis_identitas = 'PASPOR';
            $b->no_identitas = $b->penumpang->no_paspor;
            $b->alamat = $faker->address;

            $b->nomor_jaminan = random_int(10, 8929);
            $b->tanggal_jaminan = date('Y-m-d', $faker->dateTimeBetween('-2 months')->getTimestamp());

            $b->penjamin = $faker->company;
            $b->alamat_penjamin = $faker->address;
            $b->bentuk_jaminan = 'TUNAI';
            
            $b->jumlah = $faker->randomFloat(-3, 125000, 15000000);
            // $b->jenis = 'TUNAI';
            $b->tanggal_jatuh_tempo = date('Y-m-d', $faker->dateTimeBetween('+1 months', '+2 months')->getTimestamp());

            $b->nip_pembuat = $faker->numerify("##################");
            $b->nama_pembuat = $faker->name;

            $b->active = true;
            // $b->status = 'AKTIF';

            // $b->no_bukti_pengembalian = '';
            // $b->tgl_bukti_pengembalian = null
            if (random_int(0, 5) > 3) {
                // pick random CD, bind to it
                $cd = App\CD::inRandomOrder()->first();

                if (!$cd->bpj) {
                    // $b->guaranteeable()->associate($cd);
                    $cd->bpj()->save($b);
                }
            }

            // save
            $b->setNomorDokumen();
            $b->appendStatus('CREATED');
            // $b->save();
        }
        
        echo "Created {$jumlah} random BPJ(s).\n";
    }
}
