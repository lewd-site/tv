@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main user-page">
  <section class="user-page__header user-header">
  @include('users.blocks.header')
  </section>

  <section class="user-page__info user-info">
  @include('users.blocks.info')
  </section>
</main>
@endsection
