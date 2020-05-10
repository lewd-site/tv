<form class="login__inner" method="POST" action="{{ route('auth.loginSubmit') }}" enctype="multipart/form-data">
  @csrf

  <h1 class="login__title">Вход</h1>

  <div class="login__email">
    @include('common.field', [
    'type' => 'email',
    'name' => 'email',
    'label' => 'e-mail',
    'value' => old('email'),
    'attributes' => 'required maxlength="255"'
    ])
  </div>

  <div class="login__password">
    @include('common.field', [
    'type' => 'password',
    'name' => 'password',
    'label' => 'пароль',
    'value' => '',
    'attributes' => 'required minlength="8" maxlength="255"'
    ])
  </div>

  <div>
    <button type="submit" class="login__submit">
      <span>Войти</span>
    </button>
  </div>

  <a class="login__close" href="{{ route('rooms.list') }}" data-draggable="false">
    <img src="/images/close.svg" />
  </a>

  <img class="login__d-1" src="/images/d-1.svg" />
  <img class="login__d-2" src="/images/d-2.svg" />
</form>
