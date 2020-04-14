@extends('layout')

@section('title', 'LEWD.TV')

@section('content')
<main class="create-room">
  <form class="create-room__inner" method="POST" action="/create" enctype="multipart/form-data">
    @csrf

    <section class="create-room__left">
      <h2 class="create-room__title">Название комнаты</h2>

      <div class="create-room__name">
        <input type="text" class="input" name="name" required placeholder="название" />
        <span class="input-icon"></span>
        <span class="input-label">название</span>

        @error('name')
        <span class="input-error">{{ $message }}</span>
        @enderror
      </div>

      <section class="create-room__playlist">
        <h2 class="create-room__title">Плейлист</h2>

        <div class="create-room__playlist-item">
          <div class="create-room__playlist-link">
            <input type="text" class="input" name="playlist[0]" placeholder="ссылка на видео" />
            <span class="input-icon"></span>
            <span class="input-label">ссылка на видео</span>
          </div>

          <button class="create-room__playlist-edit" type="button"></button>
          <button class="create-room__playlist-handle" type="button"></button>
        </div>

        <button class="create-room__playlist-add" type="button"></button>
      </section>

      <div class="create-room__buttons">
        <button class="create-room__submit" type="submit">Создать</button>
        <a class="create-room__cancel" href="/" data-draggable="false">Отмена</a>
      </div>
    </section>

    <section class="create-room__right">
      <h3 class="create-room__label">ID комнаты</h3>

      <div class="create-room__url">
        <input type="text" class="input" name="url" required pattern="[A-Za-z0-9_-]+" placeholder="id" />
        <span class="input-icon"></span>
        <span class="input-label">id</span>

        @error('url')
        <span class="input-error">{{ $message }}</span>
        @enderror
      </div>
    </section>
  </form>
</main>
@endsection
