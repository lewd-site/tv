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
    $response = $this->get(route('rooms.list'));

    $response->assertSuccessful();
    $response->assertViewIs('rooms.pages.list');
  }

  public function test_show(): void
  {
    $url = 'anime';
    factory(Room::class)->create(['url' => $url]);

    $response = $this->get(route('rooms.show', ['url' => $url]));

    $response->assertSuccessful();
    $response->assertViewIs('rooms.pages.show');
  }

  public function test_show_notFound(): void
  {
    $response = $this->get(route('rooms.show', ['url' => 'anime']));

    $response->assertNotFound();
  }

  public function test_create(): void
  {
    $user = factory(User::class)->create();

    $response = $this->actingAs($user)->get(route('rooms.create'));

    $response->assertSuccessful();
    $response->assertViewIs('rooms.pages.create');
  }

  public function test_create_asGuest(): void
  {
    $response = $this->get(route('rooms.create'));

    $response->assertRedirect(route('auth.login'));
  }

  public function test_createSubmit(): void
  {
    $url = 'anime';
    $name = 'Anime';
    $user = factory(User::class)->create();

    $response = $this->actingAs($user)->post(route('rooms.createSubmit'), [
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

  public function test_createSubmit_asGuest(): void
  {
    $url = 'anime';
    $name = 'Anime';

    $response = $this->post(route('rooms.createSubmit'), [
      'url'  => $url,
      'name' => $name,
    ]);

    $response->assertRedirect(route('auth.login'));
  }
}
