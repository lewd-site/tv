<ul class="room-list__list">
  @foreach ($rooms as $room)
  <li class="room-list__item">
    <span class="room-list__name">{{ $room->name }}</span>
    <a class="room-list__enter" href="{{ route('rooms.show', ['url' => $room->url]) }}" data-draggable="false"></a>
  </li>
  @endforeach
</ul>
