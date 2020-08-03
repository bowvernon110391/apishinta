<?php

use App\CD;
use App\PIBK;
use Faker\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Seeder;

class BPPMSeeder extends Seeder
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

        $n = random_int(3, 8);
        echo "spawning random {$n} BPPM...\n";

        $i = 0;
        while ($i++ < $n) {
            $doctype = $f->randomElement([CD::class, PIBK::class]);

            $doc = $doctype == CD::class 
                ? CD::pure()->komersil(false)->whereDoesntHave('bppm')->inRandomOrder()->first()
                : $doctype::whereDoesntHave('bppm')->inRandomOrder()->first();

            if (!$doc) {
                echo "failed to get instance of {$doctype}. skipping...\n";
                continue;
            }

            
            try {
                // grab one, and spawn penetapan
                if (!$doc->is_locked) {
                    $r = $g->request("PUT", $doc->uri . '/penetapan', [
                        'headers' => $headers,
                        'json' => [
                            'keterangan' => "Called by BPPMSeeder @ " . date('Y-m-d H:i:s')
                        ]
                    ]);

                    if (in_array($r->getStatusCode(), [200, 204])) {
                        echo "\t{$doc->uri} berhasil ditetapkan...\n";
                    } else {
                        echo "\t{$doc->uri} gagal ditetapkan: " . $r->getReasonPhrase() . "\n";
                    }
                } else {
                    echo "\t{$doc->uri} sudah pernah ditetapkan...\n";
                }
                
                echo "{$doc->uri} spawning BPPM...\n";
                // now we make BPPM out of it
                $r = $g->request("PUT", $doc->uri . '/bppm', [
                    'headers' => $headers,
                    'json' => [
                        'lokasi' => null
                    ]
                ]);

                if (in_array($r->getStatusCode(), [200, 204] )) {
                    $b = json_decode($r->getBody());
                    // tell em
                    echo "\tBPPM terbit dengan id #{$b->id}\n";
                } else {
                    // failed
                    echo "\tgagal menerbitkan BPPM: " . $r->getReasonPhrase() . "\n";
                }
                echo "spawned one BPPM...\n";
            } catch (ClientException $e) {
                if ($e->hasResponse()) {
                    $err = json_decode($e->getResponse()->getBody());
                    echo "API Call failed: \n\t" . "({$err->error->http_code}) : {$err->error->message}" . "\n";
                }
            } catch (\Throwable $e) {
                echo "Failed: {$e->getMessage()}\n";
                continue;
            }
        }
        echo "done spawning BPPM.\n";
    }
}
