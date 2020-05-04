<div class="room-video__inner">
  <div class="room-video__main">
    <div class="room-video__player" id="player">
      <div class="room-video__video" id="video">
      </div>

      <div class="room-video__controls" id="controls" hidden>
        <div class="room-video__controls-inner">
          <div class="room-video__controls-left">
            <button type="button" class="room-video__controls-play" id="controls-play"></button>
            <button type="button" class="room-video__controls-mute" id="controls-mute"></button>

            <div class="room-video__controls-volume" id="controls-volume" data-draggable="false">
              <div class="room-video__controls-volume-fill" id="controls-volume-fill"></div>
              <div class="room-video__controls-volume-handle" id="controls-volume-handle"></div>
            </div>

            <div class="room-video__controls-time">
              @spaceless
              <span class="room-video__controls-current-time" id="controls-current-time">0:00</span>
              <span class="room-video__controls-duration" id="controls-duration">0:00</span>
              @endspaceless
            </div>

            <div class="room-video__controls-sync-off" id="controls-sync"></div>
          </div>

          <div class="room-video__controls-right">
            <button type="button" class="room-video__controls-sub-on" id="controls-sub"></button>
            <button type="button" class="room-video__controls-options" id="controls-options"></button>
            <button type="button" class="room-video__controls-cinema" id="controls-cinema"></button>
            <button type="button" class="room-video__controls-fullscreen-off" id="controls-fullscreen"></button>
          </div>

          <div class="room-video__seek" id="seek" data-draggable="false">
            <div class="room-video__seek-buffered" id="seek-buffered"></div>
            <div class="room-video__seek-fill" id="seek-fill"></div>
            <div class="room-video__seek-handle" id="seek-handle"></div>
          </div>

          <div class="room-video__player-options player-options" id="player-options" hidden>
            <div id="player-options-mount"></div>
          </div>
        </div>
      </div>

      <button class="room-video__play" id="play" hidden></button>
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
