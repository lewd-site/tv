<div class="room-video__inner">
  <div class="room-video__main">
    <div class="room-video__player"></div>

    <h2 class="room-video__title"></h2>

    <div class="room-video__info">
      <div class="room-video__avatar">
        <img class="room-video__avatar-image" data-draggable="false" />
      </div>

      <h3 class="room-video__subtitle">
        <a class="room-video__owner-name" href="{{ route('users.show', ['id' => $room->owner->id]) }}">{{ $room->owner->name }}</a>

        <span class="room-video__room-name">{{ $room->name }}</span>
      </h3>
    </div>
  </div>

  <div class="room-video__chat chat">
  </div>
</div>
