<?php

use App\CD;
use Faker\Factory;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class STSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $f = Factory::create();
        $g = new Client([
            'base_uri' => env('APP_URL'),
            'timeout' => 10
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . 'token_pdtt',
            'Content-Type' => 'application/json;charset=utf-8'
        ];

        // let's spawn random n
        $n = random_int(1,5);
        echo "generating random {$n} ST...\n";

        $i = 0;
        while($i++ < $n) {
            // call api
            $cd = CD::pure()->inRandomOrder()->first();
            // can we?
            if ($cd) {
                $id = $cd->id;
                $uri = "/cd/{$id}/st";

                echo "Calling endpoint: '{$uri}'\n";
                $r = $g->request("PUT", $uri, [
                    'headers' => $headers,
                    'json' => [
                        'keterangan' => $f->words(random_int(1, 4),true),
                        'lokasi' => $f->randomElement(['T3', 'T2F']),
                        'jenis' => $f->randomElement(['KANTOR','TERMINAL'])
                    ]
                ]);

                if ($r->getStatusCode() == 200) {
                    $b = json_decode($r->getBody());
                    echo "\tST created with id {$b->id}, uri: '{$b->uri}'";
                } else {
                    echo "\tError calling API: {$r->getReasonPhrase()}";
                }
                echo "\n";
            }
        }
        echo "Done generating ST.\n";
    }
}
