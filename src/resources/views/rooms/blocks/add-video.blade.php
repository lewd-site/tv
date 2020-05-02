<form class="add-video__inner" method="POST" action="{{ route('rooms.addVideoSubmit', ['room' => $room->url]) }}" enctype="multipart/form-data">
  @csrf

  <div class="add-video__header">
    <div class="add-video__url">
      @include('common.field', [
      'type' => 'url',
      'name' => 'url',
      'label' => 'Ссылка на видео',
      'value' => old('url'),
      'attributes' => 'required pattern="^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw]).*$" maxlength="2048"'
      ])
    </div>

    <div class="add-video__time">
      <label class="add-video__enable-start checkbox">
        <input type="checkbox" hidden />
        <span class="checkbox-icon"></span>

        <span>Начало видео</span>
      </label>

      <input type="text" class="add-video__start input" name="start" value="0:00" pattern="^(?:(?:\d+:)?[0-5]?\d:)?[0-5]?\d$" maxlength="10" />

      <label class="add-video__enable-end checkbox">
        <input type="checkbox" hidden />
        <span class="checkbox-icon"></span>

        <span>Конец видео</span>
      </label>

      <input type="text" class="add-video__end input" name="end" value="0:00" pattern="^(?:(?:\d+:)?[0-5]?\d:)?[0-5]?\d$" maxlength="10" />
    </div>
  </div>

  <div class="add-video__main">
    <div class="add-video__placeholder">
    </div>

    <div class="add-video__error" hidden>
      <h2 class="add-video__error-title">Ошибка</h2>
      <p class="add-video__error-text">Не удалось загрузить видео</p>
    </div>

    <div class="add-video__info" hidden>
      <img class="add-video__info-thumbnail" data-draggable="false" />

      <div class="add-video__info-main">
        <h2 class="add-video__info-title"></h2>
        <a href="#" class="add-video__info-author" target="_blank"></a>
      </div>
    </div>
  </div>

  <div class="add-video__footer">
    <button type="submit" class="button add-video__submit">Добавить</button>
    <a href="{{ route('rooms.show', ['room' => $room->url]) }}" class="button add-video__cancel" data-draggable="false">Отмена</a>
  </div>

  <a class="add-video__close" href="{{ route('rooms.show', ['room' => $room->url]) }}" data-draggable="false"></a>
</form>
