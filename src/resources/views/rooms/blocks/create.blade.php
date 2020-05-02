<form class="create-room__inner" method="POST" action="/create" enctype="multipart/form-data">
  @csrf

  <section class="create-room__left">
    <h2 class="create-room__title">Название комнаты</h2>

    <div class="create-room__name">
      @include('common.field', [
      'type' => 'text',
      'name' => 'name',
      'label' => 'название',
      'value' => old('name'),
      'attributes' => 'required maxlength="255"'
      ])
    </div>

    <section class="create-room__playlist">
    </section>

    <div class="create-room__buttons">
      <button class="create-room__submit" type="submit">Создать</button>
      <a class="create-room__cancel" href="{{ route('common.landing') }}" data-draggable="false">Отмена</a>
    </div>
  </section>

  <section class="create-room__right">
    <h3 class="create-room__label">URL комнаты</h3>

    <div class="create-room__url">
      @include('common.field', [
      'type' => 'text',
      'name' => 'url',
      'label' => 'URL',
      'value' => old('url'),
      'attributes' => 'required pattern="^[A-Za-z0-9_-]+$" maxlength="255"'
      ])
    </div>
  </section>
</form>
