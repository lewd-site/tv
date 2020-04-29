<form class="login__inner" method="POST" action="{{ route('auth.loginSubmit') }}" enctype="multipart/form-data">
  @csrf

  <h1 class="login__title">Вход</h1>

  <div class="login__email">
    <input type="email" class="input" name="email" value="{{ old('email') }}" required placeholder="e-mail" />
    <span class="input-icon"></span>
    <span class="input-label">e-mail</span>

    @error('email')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="login__password">
    <input type="password" class="input" name="password" value="" required minlength="8" placeholder="пароль" />
    <span class="input-icon"></span>
    <span class="input-label">пароль</span>

    @error('password')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="login__submit">
    <button type="submit" class="button button_large button_secondary">Войти</button>
  </div>

  <a class="login__close" href="{{ route('rooms.list') }}" data-draggable="false">
    <img src="/images/close.svg" />
  </a>

  <img class="login__d-1" src="/images/d-1.svg" />
  <img class="login__d-2" src="/images/d-2.svg" />
</form>
