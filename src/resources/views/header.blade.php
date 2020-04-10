<nav class="header__inner">
  <h1 class="header__logo">
    <a class="header__logo-link" href="/">
      <span>LEWD.TV</span>
      <img class="header__logo-image" src="/images/logo.svg" />
    </a>
  </h1>

  <div class="header__buttons">
    @if (Auth::check())
    <form method="POST" action="/logout" enctype="multipart/form-data">
      @csrf
      <button type="submit" class="header__button button">Выйти</button>
    </form>
    @else
    <a class="header__button button" href="/login">Вход</a>
    <a class="header__button button" href="/register">Регистрация</a>
    @endif
  </div>
</nav>
