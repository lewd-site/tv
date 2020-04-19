<div class="user-header__inner">
  <div class="user-header__avatar">
    <img class="user-header__avatar-image" src="https://www.gravatar.com/avatar/{{ md5(strtolower($user->email)) }}.jpg?s=170&d=mp" data-draggable="false" />
  </div>

  <div class="user-header__info">
    <h2 class="user-header__title">{{ $user->name }}</h2>

    <p class="user-header__text">{{ $user->rooms->count() }} комнаты</p>
  </div>
</div>
