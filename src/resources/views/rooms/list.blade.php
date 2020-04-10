@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<section class="create-room-block">
  <div class="create-room-block__inner">
    <div class="create-room-block__left">
      <h1 class="create-room-block__title">Смотри видео вместе с друзьями!</h1>

      <p class="create-room-block__text">Как кинотеатр, только лучше.</p>

      <a class="create-room-block__submit button button_primary button_large" href="/create">Создать комнату</a>
    </div>

    <div class="create-room-block__right">
      <img class="create-room-block__image" src="/images/create-room.svg" />
      <img class="create-room-block__image-overlay" src="/images/create-room.png" />
    </div>
  </div>
</section>

<main class="room-list-block">
</main>

<div class="container-wrapper">
  <footer class="container">
  </footer>
</div>
@endsection
