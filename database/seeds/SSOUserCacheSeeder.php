<?php

use App\Services\SSO;
use App\SSOUserCache;
use Illuminate\Database\Seeder;

class SSOUserCacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // gonna seed some user data (5 max)
        $numUsers = random_int(2, 5);

        $sso = app(SSO::class);
        $sso->sso->token = 'some_bullshit';

        echo "Seeding {$numUsers} SSO Users...\n";

        $i = 0;
        $cached = 0;
        while ($i++ < $numUsers) {
            // gotta call some
            $user = $sso->getUserById(random_int(1, 612));
            if ($user) {
                SSOUserCache::cacheUserData($user['data']);
                ++$cached;
            }
        }
        echo "Seeded {$cached} users.\n";
    }
}
