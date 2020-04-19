<?php

namespace Tests\Feature\Console;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
  use RefreshDatabase;

  public function test_createRoom(): void
  {
    /** @var User */
    $user = factory(User::class)->create();

    $name = 'Anime';
    $url = 'anime';
    $this->artisan('room:create')
      ->expectsQuestion('Name', $name)
      ->expectsQuestion('URL', $url)
      ->expectsQuestion("Owner's e-mail", $user->email)
      ->assertExitCode(0);

    $this->assertDatabaseHas('rooms', [
      'name'    => $name,
      'url'     => $url,
      'user_id' => $user->id,
    ]);
  }

  public function test_createRoom_conflict(): void
  {
    /** @var User */
    $user = factory(User::class)->create();

    $url = 'anime';
    factory(Room::class)->create(['url' => $url]);

    $this->artisan('room:create')
      ->expectsQuestion('Name', 'Anime')
      ->expectsQuestion('URL', $url)
      ->expectsQuestion("Owner's e-mail", $user->email)
      ->expectsOutput("Room /$url already exists")
      ->assertExitCode(1);
  }

  public function test_deleteRoom(): void
  {
    $url = 'anime';
    $room = factory(Room::class)->create(['url' => $url]);

    $this->artisan('room:delete')
      ->expectsQuestion('URL', $url)
      ->assertExitCode(0);

    $this->assertDeleted($room);
  }

  public function test_deleteRoom_notFound(): void
  {
    $url = 'anime';
    $this->artisan('room:delete')
      ->expectsQuestion('URL', $url)
      ->expectsOutput("Room /$url not found")
      ->assertExitCode(1);
  }

  public function test_listRooms(): void
  {
    $this->artisan('room:list')
      ->assertExitCode(0);
  }
}
