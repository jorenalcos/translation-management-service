<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Locale;
use App\Translation;
use App\TranslationKey;
use Faker\Generator as Faker;

$factory->define(Translation::class, function (Faker $faker) {
    return [
        'translation_key_id' => factory(TranslationKey::class),
        'locale_id' => factory(Locale::class),
        'value' => $faker->sentence,
        'is_reviewed' => $faker->boolean(70),
    ];
});
