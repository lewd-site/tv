@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main login-page">
  <section class="login-page__login login">
  @include('auth.blocks.login')
  </section>
</main>
@endsection
