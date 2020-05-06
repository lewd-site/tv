<h2 class="user-info__title">Комнаты</h2>

<ul class="user-info__list">
  @foreach ($user->rooms as $room)
  <li class="user-info__item">
    <a class="user-info__item-inner" href="{{ route('rooms.show', ['room' => $room->url]) }}" data-draggable="false">
      <span class="user-info__name">{{ $room->name }}</span>

      @if ($room->currentVideo())
      <span class="room-list__video">{{ $room->currentVideo()->title }}</span>
      @else
      <span class="room-list__video"></span>
      @endif

      <span class="user-info__user-count">{{ $room->userCount }}</span>
    </a>
  </li>
  @endforeach
</ul>
