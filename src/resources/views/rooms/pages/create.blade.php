@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main create-room-page">
  <section class="create-room-page__create-room create-room">
  @include('rooms.blocks.create')
  </section>
</main>
@endsection
