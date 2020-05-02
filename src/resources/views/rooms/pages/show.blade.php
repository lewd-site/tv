@extends('common.layout')

@push('scripts')
<script>
  window.room = <?= json_encode($room->getViewModel()); ?>;
  window.videos = <?= json_encode($videos->map(fn ($video) => $video->getViewModel())); ?>;
  window.messages = <?= json_encode($messages->map(fn ($message) => $message->getViewModel())); ?>;
</script>

<script src="{{ mix('/js/room.js') }}"></script>
<script src="{{ mix('/js/add-video.js') }}"></script>
<script src="https://www.youtube.com/iframe_api" async></script>
@endpush

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

@push('modals')
@include('rooms.modals.add-video')
@endpush
