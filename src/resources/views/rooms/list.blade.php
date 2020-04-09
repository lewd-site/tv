@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<div class="container-wrapper">
  <section class="create-room">
    <div class="create-room__left">
      <h1 class="create-room__title">Смотри видео вместе с друзьями!</h1>

      <p class="create-room__text">Как кинотеатр, только лучше.</p>

      <a class="button button_primary button_large" href="/create">Создать комнату</a>
    </div>

    <div class="create-room__right">
      <img class="create-room__image" src="/images/create-room.svg" />
      <img class="create-room__image-overlay" src="/images/create-room.png" />
    </div>
  </section>
</div>

<main class="room-list">
</main>

<div class="container-wrapper">
  <footer class="container">
  </footer>
</div>
@endsection
