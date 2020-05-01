@extends('common.layout')

@push('scripts')
<script src="{{ mix('/js/register.js') }}"></script>
@endpush

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main register-page">
  <section class="register-page__register register">
  @include('auth.blocks.register')
  </section>
</main>
@endsection
