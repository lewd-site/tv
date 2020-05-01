<form class="register__inner" method="POST" action="{{ route('auth.registerSubmit') }}" enctype="multipart/form-data">
  @csrf

  <h1 class="register__title">Регистрация</h1>

  <div class="register__name">
    @include('common.field', [
    'type' => 'text',
    'name' => 'name',
    'label' => 'имя пользователя',
    'value' => old('name'),
    'attributes' => 'required maxlength="255"'
    ])
  </div>

  <div class="register__email">
    @include('common.field', [
    'type' => 'email',
    'name' => 'email',
    'label' => 'e-mail',
    'value' => old('email'),
    'attributes' => 'required maxlength="255"'
    ])
  </div>

  <div class="register__password">
    @include('common.field', [
    'type' => 'password',
    'name' => 'password',
    'label' => 'пароль',
    'value' => '',
    'attributes' => 'required minlength="8" maxlength="255"'
    ])
  </div>

  <div class="register__confirm-password">
    @include('common.field', [
    'type' => 'password',
    'name' => 'confirm-password',
    'label' => 'повторите пароль',
    'value' => '',
    'attributes' => 'required minlength="8" maxlength="255"'
    ])
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
