@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main landing-page">
  <section class="landing-page__top-cta top-cta">
  @include('common.blocks.top-cta')
  </section>

  <section class="landing-page__info info">
  @include('common.blocks.info')
  </section>

  <section class="landing-page__bottom-cta bottom-cta">
  @include('common.blocks.bottom-cta')
  </section>
</main>
@endsection
