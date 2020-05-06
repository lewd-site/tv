<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $url
 * @property string $name
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $owner
 */
class Room extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'url',
    'name',
    'user_id',
  ];

  public function owner()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  public function videos()
  {
    return $this->hasMany(Video::class, 'room_id', 'id');
  }

  public function messages()
  {
    return $this->hasMany(ChatMessage::class, 'room_id', 'id');
  }

  public function currentVideo(): ?Video
  {
    $now = now();

    return $this->videos()
      ->where('start_at', '<=', $now)
      ->where('end_at', '>', $now)
      ->first();
  }

  public function getViewModel(): array
  {
    return [
      'id'     => $this->id,
      'url'    => $this->url,
      'name'   => $this->name,
      'userId' => $this->user_id,
    ];
  }

  /**
   * Get the route key for the model.
   *
   * @return string
   */
  public function getRouteKeyName()
  {
    return 'url';
  }
}
