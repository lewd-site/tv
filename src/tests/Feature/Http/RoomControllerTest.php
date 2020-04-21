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

  public function test_addChatMessage(): void
  {
    $url = 'room';
    /** @var Room */
    $room = factory(Room::class)->create(['url' => $url]);

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmit', ['url' => $url]);
    $response = $this->actingAs($user)->post($requestUrl, ['message' => $message]);

    $response->assertRedirect(route('rooms.show', ['url' => $url]));

    $this->assertDatabaseHas('chat_messages', [
      'message' => $message,
      'user_id' => $user->id,
      'room_id' => $room->id,
    ]);
  }

  public function test_addChatMessage_roomNotFound(): void
  {
    $url = 'room';

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmit', ['url' => $url]);
    $response = $this->actingAs($user)->post($requestUrl, ['message' => $message]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
  }

  public function test_addChatMessage_asGuest(): void
  {
    $url = 'room';
    factory(Room::class)->create(['url' => $url]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmit', ['url' => $url]);
    $response = $this->post($requestUrl, ['message' => $message]);

    $response->assertRedirect(route('auth.login'));
  }

  public function test_addChatMessageJson(): void
  {
    $url = 'room';
    /** @var Room */
    $room = factory(Room::class)->create(['url' => $url]);

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmitJson', ['url' => $url]);
    $response = $this->actingAs($user, 'api')->postJson($requestUrl, ['message' => $message]);

    $response->assertCreated();
    $response->assertHeader('Location', route('rooms.show', ['url' => $url]));
    $response->assertJson([
      'message' => $message,
      'userId'  => $user->id,
      'roomId'  => $room->id,
    ]);

    $this->assertDatabaseHas('chat_messages', [
      'message' => $message,
      'user_id' => $user->id,
      'room_id' => $room->id,
    ]);
  }

  public function test_addChatMessageJson_roomNotFound(): void
  {
    $url = 'room';

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmitJson', ['url' => $url]);
    $response = $this->actingAs($user, 'api')->postJson($requestUrl, ['message' => $message]);

    $response->assertNotFound();
  }

  public function test_addChatMessageJson_asGuest(): void
  {
    $url = 'room';
    factory(Room::class)->create(['url' => $url]);

    $message = 'Test message';

    $requestUrl = route('rooms.chatSubmitJson', ['url' => $url]);
    $response = $this->postJson($requestUrl, ['message' => $message]);

    $response->assertUnauthorized();
  }
}
