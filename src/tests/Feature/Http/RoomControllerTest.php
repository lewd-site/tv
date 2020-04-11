<?php

namespace Tests\Feature\Http;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_list(): void
  {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertViewIs('rooms.list');
  }

  public function test_show(): void
  {
    $url = 'anime';
    factory(Room::class)->create(['url' => $url]);

    $response = $this->get("/$url");

    $response->assertSuccessful();
    $response->assertViewIs('rooms.show');
  }

  public function test_create(): void
  {
    $user = factory(User::class)->create();

    $response = $this->actingAs($user)->get('/create');

    $response->assertSuccessful();
    $response->assertViewIs('rooms.create');
  }

  public function test_create_guest(): void
  {
    $response = $this->get('/create');

    $response->assertRedirect(route('auth.login'));
  }

  public function test_createSubmit(): void
  {
    $url = 'anime';
    $name = 'Anime';
    $user = factory(User::class)->create();

    $response = $this->actingAs($user)->post('/create', [
      'url'  => $url,
      'name' => $name,
    ]);

    $response->assertRedirect(route('rooms.show', ['url' => $url]));

    $this->assertDatabaseHas('rooms', [
      'url'     => $url,
      'name'    => $name,
      'user_id' => $user->id,
    ]);
  }

  public function test_createSubmit_guest(): void
  {
    $response = $this->get('/create');

    $response->assertRedirect(route('auth.login'));
  }
}
