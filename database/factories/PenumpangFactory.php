<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Penumpang;
use Faker\Generator as Faker;

$factory->define(Penumpang::class, function (Faker $faker) {
    return [
        'nama' => $faker->name(),
        'tgl_lahir' => date('Y-m-d',$faker->unixTime),
        'pekerjaan' => $faker->jobTitle,
        'no_paspor' => $faker->bothify('###???###???'),
        'kebangsaan' => 'US',
        'phone' => $faker->phoneNumber,
        'email' => $faker->email
    ];
});
