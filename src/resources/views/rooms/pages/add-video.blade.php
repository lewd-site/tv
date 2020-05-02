@extends('common.layout')

@push('scripts')
<script src="{{ mix('/js/add-video.js') }}"></script>
@endpush

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main add-video-page">
  <section class="add-video-page__add-video add-video">
    @include('rooms.blocks.add-video')
  </section>
</main>
@endsection
