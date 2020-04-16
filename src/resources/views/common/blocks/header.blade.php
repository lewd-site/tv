<nav class="header__inner">
  <div class="header__left">
    <h1 class="header__logo">
      <a class="header__logo-link" href="/" data-draggable="false">
        <span>LEWD.TV</span>
        <img class="header__logo-image" src="/images/logo.svg" />
      </a>
    </h1>
  </div>

  <div class="header__right">
    @if (Auth::check())
    <form method="POST" action="/logout" enctype="multipart/form-data">
      @csrf
      <button type="submit" class="header__button">Выйти</button>
    </form>
    @else
    <a class="header__button" href="/login" data-draggable="false">Вход</a>
    <a class="header__button" href="/register" data-draggable="false">Регистрация</a>
    @endif
  </div>
</nav>
