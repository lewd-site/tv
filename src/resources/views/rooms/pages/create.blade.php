@extends('common.layout')

@push('scripts')
<script src="{{ mix('/js/create-room.js') }}"></script>
@endpush

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main create-room-page">
  <section class="create-room-page__create-room create-room">
    @include('rooms.blocks.create')
  </section>
</main>
@endsection
