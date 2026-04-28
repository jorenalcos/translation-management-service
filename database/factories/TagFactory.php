<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Tag;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Tag::class, function (Faker $faker) {
    $name = $faker->unique()->word . '-' . $faker->unique()->numberBetween(1, 99999);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
    ];
});
