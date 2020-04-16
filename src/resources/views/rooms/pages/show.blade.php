@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main room-page">
  <section class="room-page__room room">
  @include('rooms.blocks.show')
  </section>
</main>
@endsection
