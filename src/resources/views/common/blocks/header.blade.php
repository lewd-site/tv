<nav class="header__inner">
  <div class="header__left">
    <a class="header__logo" href="{{ route('common.landing') }}" data-draggable="false">
      <img class="header__logo-image" src="/images/logo-full.svg" />
    </a>
  </div>

  <div class="header__center">
    <a class="header__link" href="{{ route('rooms.list') }}"" data-draggable="false">Комнаты</a>
    <a class="header__link" href="{{ route('common.about') }}" data-draggable="false">О сайте</a>
    <a class="header__link" href="{{ route('common.contact') }}" data-draggable="false">Контакты</a>
  </div>

  <div class="header__right">
    @if (Auth::check())
    <form method="POST" action="{{ route('auth.logout') }}"" enctype="multipart/form-data">
      @csrf
      <button type="submit" class="header__button" data-draggable="false">Выйти</button>
    </form>
    @else
    <a class="header__button" href="{{ route('auth.login') }}"" data-draggable="false">Вход</a>
    <a class="header__button" href="{{ route('auth.register') }}"" data-draggable="false">Регистрация</a>
    @endif
  </div>
</nav>
