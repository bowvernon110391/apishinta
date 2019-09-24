<?php

use Illuminate\Database\Seeder;

class PenumpangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        $jumlahPenumpang = 30;

        $i = 0;
        while($i++ < $jumlahPenumpang) {
            // create a passenger
            $p = new App\Penumpang(); 
            $p->nama        = $faker->name;
            $p->tgl_lahir   = $faker->date('Y-m-d');
            $p->pekerjaan   = $faker->jobTitle;
            $p->no_paspor   = $faker->tollFreePhoneNumber;
            $p->kebangsaan  = $faker->country;

            $p->save();
        }
    }
}
