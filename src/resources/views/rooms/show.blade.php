@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="room">
  <div class="room__inner">
    <h2 class="room__name">{{ $owner->name }} // {{ $room->name }}</h2>
  </div>
</main>
@endsection
