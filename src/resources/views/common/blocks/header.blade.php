<nav class="header__inner">
  <div class="header__left">
    <a class="header__logo" href="{{ route('common.landing') }}" data-draggable="false">
      <img class="header__logo-image" src="/images/logo-full.svg" />
    </a>
  </div>

  <div class="header__center">
    <a class="header__link" href="{{ route('rooms.list') }}" data-draggable="false">Комнаты</a>
    <a class="header__link" href="{{ route('common.about') }}" data-draggable="false">О сайте</a>
    <a class="header__link" href="{{ route('common.contact') }}" data-draggable="false">Контакты</a>
  </div>

  <div class="header__right">
    @if (Auth::check())
    <a class="header__add" href="{{ route('rooms.create') }}" data-draggable="false"></a>
    <a class="header__name" href="{{ route('users.show', ['id' => Auth::id()]) }}" data-draggable="false">{{ Auth::user()->name }}</a>

    <div class="header__avatar">
      <img class="header__avatar-image" data-draggable="false" />
    </div>

    <div class="header__menu">
      <ul class="header__list">
        <li class="header__item">
          <a href="{{ route('users.show', ['id' => Auth::id()]) }}" data-draggable="false">Профиль</a>
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
    <a class="header__button" href="{{ route('auth.login') }}" data-draggable="false">Вход</a>
    <a class="header__button" href="{{ route('auth.register') }}" data-draggable="false">Регистрация</a>
    @endif
  </div>
</nav>
