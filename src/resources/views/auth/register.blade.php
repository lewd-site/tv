@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<div class="register-form">
  <form class="login-form__inner" method="POST" action="/register" enctype="multipart/form-data">
    @csrf

    <h1 class="register-form__title">Регистрация</h1>

    <div class="register-form__name">
      <input type="text" class="input" name="name" required placeholder="name" />
    </div>

    <div class="register-form__email">
      <input type="email" class="input" name="email" required placeholder="e-mail" />
    </div>

    <div class="register-form__password">
      <input type="password" class="input" name="password" required minlength="8" placeholder="password" />
    </div>

    <div class="register-form__confirm-password">
      <input type="password" class="input" name="confirm-password" required minlength="8" placeholder="confirm password" />
    </div>

    <div class="register-form__submit">
      <button type="submit" class="button button_large button_secondary">Зарегистрироваться</button>
    </div>

    <a class="register-form__close" href="/">
      <img src="/images/close.svg" />
    </a>

    <img class="register-form__d-1" src="/images/d-1.svg" />
    <img class="register-form__d-2" src="/images/d-2.svg" />
  </form>
</div>
@endsection
