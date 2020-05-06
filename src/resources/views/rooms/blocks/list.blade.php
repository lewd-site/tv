<ul class="room-list__list">
  @foreach ($rooms as $room)
  <li class="room-list__item">
    <a class="room-list__item-inner" href="{{ route('rooms.show', ['room' => $room->url]) }}" data-draggable="false">
      <span class="room-list__name">{{ $room->name }}</span>

      @if ($room->currentVideo())
      <span class="room-list__video">{{ $room->currentVideo()->title }}</span>
      @else
      <span class="room-list__video"></span>
      @endif

      <span class="room-list__user-count">{{ $room->userCount }}</span>
    </a>
  </li>
  @endforeach
</ul>
