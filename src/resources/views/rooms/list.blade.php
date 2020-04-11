@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<section class="create-room-block">
  <div class="create-room-block__inner">
    <section class="create-room-block__left">
      <h1 class="create-room-block__title">Смотри видео вместе с друзьями!</h1>

      <p class="create-room-block__text">Как кинотеатр, только лучше.</p>

      <a class="create-room-block__submit" href="/create" data-draggable="false">
        Создать комнату
      </a>
    </section>

    <section class="create-room-block__right">
      <img class="create-room-block__image" src="/images/create-room.svg" />
      <img class="create-room-block__image-overlay" src="/images/create-room.png" />
    </section>
  </div>
</section>

<main class="room-list-block">
  <ul class="room-list-block__list">
    @foreach ($rooms as $room)
    <li class="room-list-block__item">
      <span class="room-list-block__name">{{ $room->name }}</span>
      <a class="room-list-block__enter" href="{{ route('rooms.show', ['url' => $room->url]) }}" data-draggable="false"></a>
    </li>
    @endforeach
  </ul>
</main>

<footer class="footer">
  <div class="footer__inner">
  </div>
</footer>
@endsection
