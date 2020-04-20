<div class="chat__inner">
  <div class="chat__header">
    <div class="chat__header-left"></div>

    <div class="chat__header-right">
      <span class="chat__count">0 online</span>
    </div>
  </div>

  <div class="chat__main">
    <ul class="chat__list">
      @foreach ($room->messages as $message)
      <li class="chat__item">
        <div class="chat__avatar">
          <img class="chat__avatar-image" src="https://www.gravatar.com/avatar/{{ md5(strtolower($message->user->email)) }}.jpg?s=24&d=mp" data-draggable="false" />
        </div>

        <div class="chat__message">
          <a class="chat__name" href="{{ route('users.show', ['id' => $message->user_id]) }}">{{ $message->user->name }}</a>
          <span class="chat__message-text">{{ $message->message }}</span>
        </div>
      </li>
      @endforeach
    </ul>
  </div>

  <div class="chat__footer">
    <form class="chat__form" method="POST" action="{{ route('rooms.chatSubmit', ['url' => $room->url]) }}" enctype="multipart/form-data">
      @csrf

      <input type="text" class="input chat__input" name="message" />

      <button type="submit" hidden>Отправить</button>
    </form>
  </div>
</div>