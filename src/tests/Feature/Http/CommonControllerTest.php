<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

class CommonControllerTest extends TestCase
{
  public function test_landing(): void
  {
    $response = $this->get(route('common.landing'));

    $response->assertSuccessful();
    $response->assertViewIs('common.pages.landing');
  }

  public function test_about(): void
  {
    $response = $this->get(route('common.about'));

    $response->assertSuccessful();
    $response->assertViewIs('common.pages.about');
  }

  public function test_contact(): void
  {
    $response = $this->get(route('common.contact'));

    $response->assertSuccessful();
    $response->assertViewIs('common.pages.contact');
  }

  public function test_donate(): void
  {
    $response = $this->get(route('common.donate'));

    $response->assertSuccessful();
    $response->assertViewIs('common.pages.donate');
  }
}
