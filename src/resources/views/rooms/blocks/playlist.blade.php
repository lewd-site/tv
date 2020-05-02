<div class="room-playlist__inner">
  @if (auth()->check() && auth()->id() === $room->user_id)
  <a href="{{ route('rooms.addVideo', ['room' => $room->url]) }}" class="room-playlist__add" data-draggable="false">Добавить видео</a>
  @endif

  <h2 class="room-playlist__title">Плейлист</h2>

  @spaceless
  <ul class="room-playlist__list">
    @foreach ($videos as $video)
    <li class="room-playlist__item">
      <span class="room-playlist__item-title">{{ $video->title }}</span>
    </li>
    @endforeach
  </ul>
  @endspaceless
</div>
