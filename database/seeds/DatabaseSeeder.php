<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(PenumpangSeeder::class);
        $this->call(KursSeeder::class);
        $this->call(SSOUserCacheSeeder::class);
        $this->call(CDSeeder::class);
    }
}
