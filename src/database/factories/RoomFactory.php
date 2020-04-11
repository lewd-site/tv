<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Room;
use App\Models\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Room::class, function (Faker $faker) {
  $user = factory(User::class)->create();

  return [
    'url'     => $faker->slug,
    'name'    => $faker->unique()->safeEmail,
    'user_id' => $user->id,
  ];
});
