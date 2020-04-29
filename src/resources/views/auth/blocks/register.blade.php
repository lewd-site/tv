<form class="register__inner" method="POST" action="{{ route('auth.registerSubmit') }}" enctype="multipart/form-data">
  @csrf

  <h1 class="register__title">Регистрация</h1>

  <div class="register__name">
    <input type="text" class="input" name="name" value="{{ old('name') }}" required placeholder="имя пользователя" />
    <span class="input-icon"></span>
    <span class="input-label">имя пользователя</span>

    @error('name')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="register__email">
    <input type="email" class="input" name="email" value="{{ old('email') }}"  required placeholder="e-mail" />
    <span class="input-icon"></span>
    <span class="input-label">e-mail</span>

    @error('email')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="register__password">
    <input type="password" class="input" name="password" value=""  required minlength="8" placeholder="пароль" />
    <span class="input-icon"></span>
    <span class="input-label">пароль</span>

    @error('password')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="register__confirm-password">
    <input type="password" class="input" name="confirm-password" value=""  required minlength="8" placeholder="повторите пароль" />
    <span class="input-icon"></span>
    <span class="input-label">повторите пароль</span>

    @error('confirm-password')
    <span class="input-error">{{ $message }}</span>
    @enderror
  </div>

  <div class="register__submit">
    <button type="submit" class="button button_large button_secondary">Зарегистрироваться</button>
  </div>

  <a class="register__close" href="{{ route('rooms.list') }}" data-draggable="false">
    <img src="/images/close.svg" />
  </a>

  <img class="register__d-1" src="/images/d-1.svg" />
  <img class="register__d-2" src="/images/d-2.svg" />
</form>
