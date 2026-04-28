<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TranslationKey;
use Faker\Generator as Faker;

$factory->define(TranslationKey::class, function (Faker $faker) {
    return [
        'key' => 'app.' . $faker->unique()->slug(3),
        'description' => $faker->sentence,
    ];
});
