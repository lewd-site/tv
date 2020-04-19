<ul class="room-list__list">
  @foreach ($rooms as $room)
  <li class="room-list__item">
    <a class="room-list__name" href="{{ route('rooms.show', ['url' => $room->url]) }}" data-draggable="false">{{ $room->name }}</a>
  </li>
  @endforeach
</ul>
