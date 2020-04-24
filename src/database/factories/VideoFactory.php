<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

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

$factory->define(Video::class, function (Faker $faker) {
  $room = factory(Room::class)->create();
  $user = factory(User::class)->create();

  $startAt = new Carbon($faker->dateTimeThisMonth);
  $endAt = $startAt->clone()->addMinutes(10);

  return [
    'url'      => $faker->slug,
    'type'     => 'youtube',
    'title'    => $faker->sentence,
    'start_at' => $startAt,
    'end_at'   => $endAt,
    'room_id'  => $room->id,
    'user_id'  => $user->id,
  ];
});
