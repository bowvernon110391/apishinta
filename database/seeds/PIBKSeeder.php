<?php

use App\Gudang;
use App\PJT;
use App\SPP;
use App\ST;
use Faker\Factory;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class PIBKSeeder extends Seeder
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
            'timeout' => 15
        ]);
        $headers = [
            'Authorization' => 'Bearer token_pdtt',
            'Content-Type' => 'application/json'
        ];

        $n = random_int(3,5);
        echo "generating random {$n} PIBK...\n";
        
        $i = 0;
        while($i++ < $n) {
            // just choose random SPP/ST
            // and create PIBK out of it
            $doctype = $f->randomElement([SPP::class, ST::class]);

            $doc = $doctype::whereDoesntHave('pibk')->inRandomOrder()->first();

            if (!$doc) {
                continue;
            }

            // make a pibk out of it
            $uri = $doc->uri . '/pibk';
            echo "Calling endpoint: '$uri'\n";
            $r = $g->request("PUT", $uri, [
                'headers' => $headers,
                'json' => [
                    'lokasi_type' => Gudang::class,
                    'lokasi_id' => Gudang::inRandomOrder()->first()->id,
                    'pemberitahu_id' => null,
                    'pemberitahu_type' => PJT::class
                ]
            ]);

            if ($r->getStatusCode() == 200) {
                $b = json_decode($r->getBody());
                echo "\tPIBK created with id {$b->id}, uri: '{$b->uri}'";
            } else {
                echo "\tError calling API: {$r->getReasonPhrase()}";
            }
            echo "\n";
        }
        echo "Done generating PIBK.\n";
    }
}
