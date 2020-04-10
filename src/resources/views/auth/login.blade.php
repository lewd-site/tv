@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<div class="login-form">
  <form class="login-form__inner" method="POST" action="/login" enctype="multipart/form-data">
    @csrf

    <h1 class="login-form__title">Вход</h1>

    <div class="login-form__email">
      <input type="email" class="input" name="email" required placeholder="e-mail" />
    </div>

    <div class="login-form__password">
      <input type="password" class="input" name="password" required minlength="8" placeholder="password" />
    </div>

    <div class="login-form__submit">
      <button type="submit" class="button button_large button_secondary">Войти</button>
    </div>

    <a class="login-form__close" href="/">
      <img src="/images/close.svg" />
    </a>

    <img class="login-form__d-1" src="/images/d-1.svg" />
    <img class="login-form__d-2" src="/images/d-2.svg" />
  </form>
</div>
@endsection
