@extends('common.layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="layout__main about-page">
  <section class="about-page__about about">
  @include('common.blocks.about')
  </section>

  <img class="about-page__image" src="/images/about.svg" />
</main>
@endsection
