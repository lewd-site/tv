@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main room-list-page">
  <section class="room-list-page__create-room create-room-cta">
  @include('rooms.blocks.create-cta')
  </section>

  <section class="room-list-page__room-list room-list">
  @include('rooms.blocks.list')
  </section>
</main>
@endsection
