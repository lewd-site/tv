<h2 class="user-info__title">Комнаты</h2>

<ul class="user-info__list">
  @foreach ($user->rooms as $room)
  <li class="user-info__item">
    <a class="user-info__name" href="{{ route('rooms.show', ['url' => $room->url]) }}" data-draggable="false">{{ $room->name }}</a>
  </li>
  @endforeach
</ul>
