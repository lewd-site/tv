<div class="room-video__inner">
  <div class="room-video__main">
    <div class="room-video__player">
      <div class="room-video__video" id="player">
      </div>

      <button class="room-video__play" hidden></button>
    </div>

    <h2 class="room-video__title"></h2>

    <div class="room-video__info">
      <div class="room-video__avatar">
        <img class="room-video__avatar-image" src="https://www.gravatar.com/avatar/{{ md5(strtolower($room->owner->email)) }}.jpg?s=72&d=mp" data-draggable="false" />
      </div>

      <h3 class="room-video__subtitle">
        <a class="room-video__owner-name" href="{{ route('users.show', ['user' => $room->owner->id]) }}">{{ $room->owner->name }}</a>

        <span class="room-video__room-name">{{ $room->name }}</span>
      </h3>
    </div>
  </div>

  <div class="room-video__chat chat">
    @include('rooms.blocks.chat')
  </div>
</div>
