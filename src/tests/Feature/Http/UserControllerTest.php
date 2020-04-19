<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_show(): void
  {
    $user = factory(User::class)->create();

    $response = $this->get(route('users.show', ['id' => $user->id]));

    $response->assertSuccessful();
    $response->assertViewIs('users.pages.show');
  }

  public function test_show_notFound(): void
  {
    $response = $this->get(route('users.show', ['id' => 1]));

    $response->assertNotFound();
  }
}
