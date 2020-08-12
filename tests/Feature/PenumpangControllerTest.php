<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenumpangControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    /**
     * index penumpang without auth should fail with code 401
     */
    public function testIndexPenumpangWithoutAuth()
    {
        $response = $this->get('/penumpang');

        $response->assertStatus(401);
    }

    /**
     * index penumpang with mock token should success (200)
     */
    public function testIndexPenumpang() {
        $response = $this->get('/penumpang', [
            'Authorization' => 'Bearer token_admin'
        ]);

        $response->assertStatus(200);
    }

    /**
     * input penumpang without auth should fail
     */
    public function testInputPenumpangWithoutAuth() {
        $data = [
            'nama' => $this->faker->name(),
            'tgl_lahir' => date('Y-m-d'),
            'pekerjaan' => '-',
            'no_paspor' => '123123123123',
            'kebangsaan' => 'ID'
        ];

        $response = $this->postJson('/penumpang', $data);

        $response->assertStatus(401);
    }

    /**
     * input penumpang with auth but without contacts should fail
     */
    public function testInputPenumpangWithAuthWithoutContacts() {
        $data = [
            'nama' => $this->faker->name(),
            'tgl_lahir' => date('Y-m-d'),
            'pekerjaan' => '-',
            'no_paspor' => '123123123123',
            'kebangsaan' => 'ID'
        ];

        $response = $this->postJson('/penumpang', $data, [
            'Authorization' => 'Bearer token_admin'
        ]);

        $response->assertStatus(400);
    }

    /**
     * input penumpang with auth and contacts should succeed
     */
    public function testInputPenumpangWithAuth() {
        $data = [
            'nama' => $this->faker->name(),
            'tgl_lahir' => date('Y-m-d'),
            'pekerjaan' => '-',
            'no_paspor' => '123123123123',
            'kebangsaan' => 'ID',
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber
        ];

        // dump($data);

        $response = $this->postJson('/penumpang', $data, [
            'Authorization' => 'Bearer token_admin',
            'Content-type' => 'application/json'
        ]);

        // dump($response);

        $response->assertStatus(200);
    }
}
