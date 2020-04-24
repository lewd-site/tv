<div class="room-playlist__inner">
  <h2 class="room-playlist__title">Плейлист</h2>

  <ul class="room-playlist__list">
    @foreach ($videos as $video)
    <li class="room-playlist__item">
      <span class="room-playlist__item-title">{{ $video->title }}</span>
    </li>
    @endforeach
  </ul>

  @if (auth()->check() && auth()->id() === $room->user_id)
  <button class="room-playlist__add" type="button"></button>
  @endif
</div>

@push('modals')
<div class="add-video-modal" hidden>
  <div class="add-video-modal__inner">
    <form method="POST" action="{{ route('rooms.videoSubmit', ['url' => $room->url]) }}" enctype="multipart/form-data">
      @csrf

      <input type="text" class="add-video-modal__url input" name="url" placeholder="Ссылка на видео" required />

      <button type="submit" class="add-video-modal__submit">Добавить</button>
    </form>

    <span class="add-video-modal__title"></span>

    <a class="add-video-modal__close" href="{{ route('rooms.show', ['url' => $room->url]) }}" data-draggable="false">
      <img src="/images/close.svg" />
    </a>
  </div>
</div>
@endpush
