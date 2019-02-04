<?php

use Faker\Generator as Faker;

$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->text(20),
        'shopify_id' => $faker->randomNumber(),
        'body' => $faker->randomHtml(),
        'image_id' => factory('App\ProductImage')->create(),
    ];
});
