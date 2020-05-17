<nav class="header__inner">
  <div class="header__left">
    <a class="header__logo" href="{{ route('common.landing') }}">
      <img class="header__logo-image" src="/images/logo-full.svg" data-draggable="false" />
    </a>
  </div>

  <div class="header__center">
    <a class="header__link" href="{{ route('rooms.list') }}">Комнаты</a>
    <a class="header__link" href="{{ route('common.about') }}">О сайте</a>
    <a class="header__link" href="{{ route('common.contact') }}">Контакты</a>
    <a class="header__link" href="{{ route('common.faq') }}">FAQ</a>
  </div>

  <div class="header__right">
    @if (Auth::check())
    <a class="header__name" href="{{ route('users.show', ['user' => Auth::id()]) }}" data-draggable="false">{{ Auth::user()->name }}</a>

    <div class="header__avatar">
      <img class="header__avatar-image" src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}.jpg?s=32&d=mp" data-draggable="false" />
    </div>

    <div class="header__menu">
      <ul class="header__list">
        <li class="header__item">
          <a href="{{ route('users.show', ['user' => Auth::id()]) }}" data-draggable="false">Профиль</a>
        </li>

        <li class="header__item">
          <a href="{{ route('rooms.create') }}" data-draggable="false">Создать комнату</a>
        </li>

        <li class="header__item header__item_red">
          <form method="POST" action="{{ route('auth.logout') }}" enctype="multipart/form-data">
            @csrf
            <button type="submit" data-draggable="false">Выход</button>
          </form>
        </li>
      </ul>
    </div>
    @else
    <a class="header__login" href="{{ route('auth.login') }}" data-draggable="false">
      <span>Вход</span>
    </a>

    <a class="header__register" href="{{ route('auth.register') }}" data-draggable="false">
      <span>Регистрация</span>
    </a>
    @endif
  </div>
</nav>
