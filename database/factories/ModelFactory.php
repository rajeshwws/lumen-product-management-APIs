<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => Hash::make('password')
    ];
});

$factory->define(\App\Models\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
        'sku' => $faker->unique()->ean8,
        'qty' => 10,
        'description' => $faker->sentence,
        'product_type_id' => function () {
            return rand(1, 2);
        }
    ];
});

$factory->define(\App\Models\Order::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'address' => $faker->address,
        'payment_method' => $faker->creditCardType,
        'total_price' => 1000
    ];
});
