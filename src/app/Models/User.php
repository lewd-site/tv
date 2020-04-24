<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
  use Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password',
  ];

  public function rooms()
  {
    return $this->hasMany(Room::class, 'user_id', 'id');
  }

  public function videos()
  {
    return $this->hasMany(Video::class, 'user_id', 'id');
  }

  public function messages()
  {
    return $this->hasMany(ChatMessage::class, 'user_id', 'id');
  }

  public function getViewModel(): array
  {
    return [
      'id'    => $this->id,
      'name'  => $this->name,
      'email' => $this->email,
    ];
  }
}
