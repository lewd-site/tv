@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main room-list-page">
  <section class="room-list-page__room-list room-list">
    @include('rooms.blocks.list')
  </section>

  <div class="room-list-page__buttons">
    <a class="room-list-page__button" href="{{ route('rooms.create') }}">
      <span>Создать комнату</span>
    </a>
  </div>
</main>
@endsection
