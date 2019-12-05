<?php

use Illuminate\Database\Seeder;

class KursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $knownValuta = [
            'USD',
            'INR',
            'KRW',
            'JPY',
            'GBP',
            'CNY',
            'THB',
            'SGD'
        ];

        // seed how many? as many as possible
        $faker = Faker\Factory::create();
        $jumlah = 50;

        echo "Seeding {$jumlah} rows of Kurs...\n";

        $i = 0;
        while ($i++ < $jumlah) {
            $tmpTanggalAwal = $faker->dateTimeBetween('-2 months', 'now');
            $kurs = new App\Kurs([
                'kode_valas' => $faker->randomElement($knownValuta),
                'kurs_idr'  => $faker->randomFloat(4, 99.99, 19000.99),
                'jenis'     => 'KURS_PAJAK',
                'tanggal_awal'  => $tmpTanggalAwal->format('Y-m-d'),
                'tanggal_akhir'  => $tmpTanggalAwal->modify('+1 week')->format('Y-m-d')
            ]);
            
            try {
                $kurs->save();
            } catch (\Exception $e) {
                echo "Skipping duplicate kurs....{$kurs->kode_valas}\n";
            }
        }

        echo "Data Kurs seeded.\n";
    }
}
