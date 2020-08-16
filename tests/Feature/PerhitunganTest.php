<?php

namespace Tests\Feature;

use App\CD;
use App\DeclareFlag;
use App\DetailBarang;
use App\HsCode;
use App\Kurs;
use App\Lokasi;
use App\Penumpang;
use App\ReferensiJenisPungutan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerhitunganTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * this will generate a proper CD with personal use
     */
    private function generateCD(bool $personalUse = true) {
        $c = new CD([
            'tgl_dok' => date('Y-m-d'),
            'no_hp' => '',
            'npwp' => '',
            'nib' => '',
            'alamat' => $this->faker->address,
            'no_flight' => $this->faker->bothify('GA###'),
            'kd_pelabuhan_asal' => 'USJFK',
            'kd_pelabuhan_tujuan' => 'IDCGK',
            'jml_anggota_keluarga' => 1,
            'jml_bagasi_dibawa' => 2,
            'jml_bagasi_tdk_dibawa' => 2,
            'koli' => 2,
            'pph_tarif' => 7.5,
            'kd_airline' => 'GA'
        ]);

        // associate lokasi
        $c->lokasi()->associate(Lokasi::byKode('T3')->first());

        // create penumpang too?
        $p = factory(Penumpang::class)->create();
        $c->penumpang()->associate($p);

        // ndpbm, also set its value?
        $usd = Kurs::updateOrCreate([
            'kode_valas' => 'USD',
            'kurs_idr' => 13445.0,
            'jenis' => 'KURS_PAJAK',
            'tanggal_awal' => Date('Y-m-d'),
            'tanggal_akhir' => Date('Y-m-d')
        ]);
        $c->ndpbm()->associate($usd);

        $c->save();

        // add some detail barang? nope left for each case
        if ($personalUse) {
            $c->declareFlags()->save(DeclareFlag::byName('IMPOR_UNTUK_DIPAKAI')->first());
        } else {
            $c->declareFlags()->save(DeclareFlag::byName('KOMERSIL')->first());
        }

        return $c;
    }

    /**
     * Tes perhitungan Skema Pembebasan 1 (agregat)
     */
    public function testHitungSkemaPembebasan1()
    {
        $c = $this->generateCD();
        $c->pembebasan = 500;
        $c->save();

        // first, gotta make sure it's non commercial
        $this->assertTrue(!$c->komersil);

        // generate several details
        $barang = [
            [
                'uraian' => 'gantungan kunci',
                'fob' => 60,
                'freight' => 10,
                'insurance' => 5
            ],
            [
                'uraian' => 'kamera',
                'fob' => 650,
                'freight' => 0,
                'insurance' => 0
            ],
            [
                'uraian' => 'head speaker',
                'fob' => 400,
                'freight' => 0,
                'insurance' => 0
            ]
        ];

        foreach ($barang as $b) {
            // append to it?
            $d = new DetailBarang([
                'uraian' => $b['uraian'],
                'jumlah_kemasan' => 30,
                'jenis_kemasan' => 'PK',
                'fob' => $b['fob'],
                'hs_id' => 10745,
                'insurance' => $b['insurance'],
                'freight' => $b['freight'],
                'brutto' => 0.5,
                'kurs_id' => $c->ndpbm->id
            ]);
            $c->detailBarang()->save($d);
        }

        // ensure that the detail barang count is 3
        $this->assertCount(3, $c->detailBarang, 'Jumlah Detail Barang');

        $pungutan = $c->computePungutanCdPersonal();

        echo "Dumping calculation for Skema Pembebasan 1 (agregat):\n";
        dump($pungutan);

        // make sure our calculation is right, if it does, these two keys must exist
        $this->assertArrayHasKey('pembebasan', $pungutan, "Nilai Pembebasan (USD)");
        $this->assertArrayHasKey('pungutan', $pungutan, 'Pungutan Impor CD Personal');

        // make sure pungutan consists of bm, ppn, pph
        $this->assertArrayHasKey('bm', $pungutan['pungutan'], 'Perhitungan BM');
        $this->assertArrayHasKey('ppn', $pungutan['pungutan'], 'Perhitungan PPN');
        $this->assertArrayHasKey('pph', $pungutan['pungutan'], 'Perhitungan PPh');

        // make sure the value is right
        $this->assertEquals(841000, $pungutan['pungutan']['bm'], 'Nilai BM');
        $this->assertEquals(925000, $pungutan['pungutan']['ppn'], 'Perhitungan PPN');
        $this->assertEquals(694000, $pungutan['pungutan']['pph'], 'Perhitungan PPh');
    }

    /**
     * Tes perhitungan Skema MFN (non-personal use)
     */
    public function testHitungSkemaMFN2() {
        $c = $this->generateCD(false);

        $c->ndpbm->kurs_idr = 13000;
        
        $c->push();

        // make sure it's komersil
        $this->assertTrue($c->komersil);

        // add detail barang
        // generate several details
        $barang = [
            [
                'uraian' => 'Gear',
                'fob' => 100,
                'freight' => 5,
                'insurance' => 2,
                'hscode' => '87141040'
            ],
            [
                'uraian' => 'Shockbreaker',
                'fob' => 200,
                'freight' => 5,
                'insurance' => 2,
                'hscode' => '87141090'
            ],
            [
                'uraian' => 'Knalpot',
                'fob' => 125,
                'freight' => 5,
                'insurance' => 2,
                'hscode' => '87141090'
            ]
        ];

        foreach ($barang as $b) {
            // append to it?
            $d = new DetailBarang([
                'uraian' => $b['uraian'],
                'jumlah_kemasan' => 30,
                'jenis_kemasan' => 'PK',
                'fob' => $b['fob'],
                'hs_id' => HsCode::usable()->byExactHS($b['hscode'])->first()->id,
                'insurance' => $b['insurance'],
                'freight' => $b['freight'],
                'brutto' => 0.5,
                'kurs_id' => $c->ndpbm->id
            ]);
            $c->detailBarang()->save($d);
        }

        // ensure 3 detail barang
        $this->assertCount(3, $c->detailBarang, 'Jumlah Detail Barang');

        $pungutan = $c->computePungutanCdKomersil();

        // dump it
        echo "Dumping calculation for Skema MFN 2:\n";
        dump($pungutan);

        // make sure it doesn't have pembebasan
        $this->assertArrayNotHasKey('pembebasan', $pungutan, 'Nilai Pembebasan');

        // make sure it has summary pungutan
        $this->assertArrayHasKey('pungutan', $pungutan, 'Pungutan Impor CD Komersil');
        $this->assertArrayHasKey('bayar', $pungutan['pungutan'], 'Data Pungutan Bayar');

        // make sure it consists of BM, PPN, PPh
        $this->assertArrayHasKey('BM', $pungutan['pungutan']['bayar'], 'Perhitungan BM');
        $this->assertArrayHasKey('PPN', $pungutan['pungutan']['bayar'], 'Perhitungan PPN');
        $this->assertArrayHasKey('PPh', $pungutan['pungutan']['bayar'], 'Perhitungan PPh');

        $this->assertEquals(512000, $pungutan['pungutan']['bayar']['BM'], 'Nilai BM');
        $this->assertEquals(633000, $pungutan['pungutan']['bayar']['PPN'], 'Perhitungan PPN');
        $this->assertEquals(475000, $pungutan['pungutan']['bayar']['PPh'], 'Perhitungan PPh');
    }

    /**
     * Test skema perhitungan Pembebasan Proporsional (PDTT-KEP KK)
     */
    public function testHitungSkemaPembebasanProporsional3() {
        $c = $this->generateCD();

        $c->ndpbm->kurs_idr = 14661;
        $c->pembebasan = 500;
        
        $c->push();

        // make sure it's non komersil
        $this->assertTrue(!$c->komersil);

        // make sure ndpbm set to correct value
        $this->assertEquals($c->ndpbm->kurs_idr,14661,'Kurs USD');

        // add detail barang sesuai contoh pdtt

        // 1. Sepatu
        $d = new DetailBarang([
            'uraian' => "Sepatu",
            'jumlah_kemasan' => 1,
            'jenis_kemasan' => 'PK',
            'fob' => 750,
            'hs_id' => HsCode::usable()->byExactHS('87141090')->first()->id,
            'insurance' => 0,
            'freight' => 0,
            'brutto' => 0.5,
            'kurs_id' => $c->ndpbm->id
        ]);
        $c->detailBarang()->save($d);
        $d->refresh();
        $d->tarif()->create([
            'jenis_pungutan_id' => ReferensiJenisPungutan::byKode('PPh')->first()->id,
            'tarif' => 10.0,
            'bayar' => 100.0
        ]);

        // 2. Mainan
        $d = new DetailBarang([
            'uraian' => "Mainan",
            'jumlah_kemasan' => 1,
            'jenis_kemasan' => 'PK',
            'fob' => 135,
            'hs_id' => HsCode::usable()->byExactHS('87141090')->first()->id,
            'insurance' => 0,
            'freight' => 0,
            'brutto' => 0.5,
            'kurs_id' => $c->ndpbm->id
        ]);
        $c->detailBarang()->save($d);

        // 3. Kaos
        $d = new DetailBarang([
            'uraian' => "Kaos",
            'jumlah_kemasan' => 1,
            'jenis_kemasan' => 'PK',
            'fob' => 145,
            'hs_id' => HsCode::usable()->byExactHS('87141090')->first()->id,
            'insurance' => 0,
            'freight' => 0,
            'brutto' => 0.5,
            'kurs_id' => $c->ndpbm->id
        ]);
        $c->detailBarang()->save($d);
        
        // 4. Parfum
        $d = new DetailBarang([
            'uraian' => "Sepatu",
            'jumlah_kemasan' => 1,
            'jenis_kemasan' => 'PK',
            'fob' => 200,
            'hs_id' => HsCode::usable()->byExactHS('87141090')->first()->id,
            'insurance' => 0,
            'freight' => 0,
            'brutto' => 0.5,
            'kurs_id' => $c->ndpbm->id
        ]);
        $c->detailBarang()->save($d);
        $d->refresh();
        $d->tarif()->create([
            'jenis_pungutan_id' => ReferensiJenisPungutan::byKode('PPh')->first()->id,
            'tarif' => 10.0,
            'bayar' => 100.0
        ]);

        // ensure 3 detail barang
        $this->assertCount(4, $c->detailBarang, 'Jumlah Detail Barang');

        // set pembebasan
        $c->setPembebasanProporsional();
        $pungutan = $c->computePungutanCdPembebasanProporsional();

        // dump it
        echo "Dumping calculation for Skema Pembebasan Proporsional 3:\n";
        dump($pungutan);

        // make sure it has pembebasan
        $this->assertArrayHasKey('pembebasan', $pungutan, 'Pembebasan CD Personal');
        $this->assertArrayHasKey('pembebasan', $pungutan['pembebasan'], 'Nilai Pembebasan USD');
        $this->assertArrayHasKey('pembebasan_idr', $pungutan['pembebasan'], 'Nilai Pembebasan IDR');


        $this->assertEquals(500.0, $pungutan['pembebasan']['pembebasan'], 'Nilai Pembebasan USD');
        $this->assertEquals(7330500.0, $pungutan['pembebasan']['pembebasan_idr'], 'Nilai Pembebasan IDR');

        // make sure it has summary pungutan
        $this->assertArrayHasKey('pungutan', $pungutan, 'Pungutan Impor CD Personal Skema Pembebasan Proporsional');
        $this->assertArrayHasKey('bayar', $pungutan['pungutan'], 'Data Pungutan Bayar');

        // make sure it consists of BM, PPN, PPh
        $this->assertArrayHasKey('BM', $pungutan['pungutan']['bayar'], 'Perhitungan BM');
        $this->assertArrayHasKey('PPN', $pungutan['pungutan']['bayar'], 'Perhitungan PPN');
        $this->assertArrayHasKey('PPh', $pungutan['pungutan']['bayar'], 'Perhitungan PPh');

        $this->assertEquals(1073000, $pungutan['pungutan']['bayar']['BM'], 'Nilai BM');
        $this->assertEquals(1179000, $pungutan['pungutan']['bayar']['PPN'], 'Perhitungan PPN');
        $this->assertEquals(1112000, $pungutan['pungutan']['bayar']['PPh'], 'Perhitungan PPh');
    }
}
