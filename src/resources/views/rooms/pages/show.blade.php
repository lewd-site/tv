@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main room-page">
  <section class="room-page__video room-video">
  @include('rooms.blocks.video')
  </section>

  <section class="room-page__playlist room-playlist">
  @include('rooms.blocks.playlist')
  </section>
</main>
@endsection
