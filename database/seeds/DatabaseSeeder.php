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
        // $this->call(SSPCPSeed::class);
        // $this->call(ISSeed::class);
        // $this->call(SPMBSeed::class);
        // $this->call(BPJSeeder::class);
        $this->call(PembatalanSeeder::class);
    }
}
