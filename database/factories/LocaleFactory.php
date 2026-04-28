<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Locale;
use Faker\Generator as Faker;

$factory->define(Locale::class, function (Faker $faker) {
    return [
        'code' => strtolower($faker->unique()->bothify('??_##')),
        'name' => $faker->country,
        'is_active' => true,
    ];
});
