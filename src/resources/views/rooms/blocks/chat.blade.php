<div class="chat__inner">
  <div class="chat__header">
    <div class="chat__header-left"></div>

    <div class="chat__header-right">
      <span class="chat__count">{{ $userCount }} online</span>
    </div>
  </div>

  @spaceless
  <div class="chat__main">
    <ul class="chat__list">
      @php
      $today = now()->format('Y-m-d');
      $yesterday = now()->subDay()->format('Y-m-d');
      @endphp
      @foreach ($messages->groupBy(fn ($message) => $message->created_at->format('Y-m-d')) as $date => $dateMessages)
      <li class="chat__date-separator">
        @if ($date === $today)
        Сегодня
        @elseif ($date === $yesterday)
        Вчера
        @else
        {{ (new Carbon($date))->format('j F') }}
        @endif
      </li>
      @endspaceless
      @spaceless
      @foreach ($dateMessages as $message)
      <li class="chat__item">
        <div class="chat__message">
          <span class="chat__name">{{ $message->user->name }}</span>
          @endspaceless
          @spaceless
          <span class="chat__message-text">{{ $message->message }}</span>
        </div>
        @endspaceless
        @spaceless
        <div class="chat__message-right">
          <button type="button" class="chat__message-mention">@</button>
          @endspaceless
          @spaceless
          <time class="chat__message-time" datetime="{{ $message->created_at->format('c') }}">{{ $message->created_at->format('H:i') }}</time>
        </div>
      </li>
      @endforeach
      @endforeach
    </ul>
  </div>
  @endspaceless

  <div class="chat__footer">
    <form class="chat__form" method="POST" action="{{ route('rooms.chatSubmit', ['room' => $room->url]) }}" enctype="multipart/form-data">
      @csrf

      <input type="text" class="input chat__input" name="message" value="{{ old('message') }}" required maxlength="1000" autocomplete="off" />

      <button type="submit" hidden>Отправить</button>
    </form>
  </div>

  <ul class="chat__popup-list" hidden>
    <li class="chat__popup-item">
      <a class="chat__popup-link" href="#" target="_blank">
        <div class="chat__avatar">
          <img class="chat__avatar-image" data-draggable="false" />
        </div>

        <span>Профиль</span>
      </a>
    </li>
  </ul>
</div>
