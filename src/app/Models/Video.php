<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $url
 * @property string $type
 * @property string $title
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property int $user_id
 * @property int $room_id
 * @property User $user
 * @property Room $room
 */
class Video extends Model
{
  public $timestamps = false;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'url',
    'type',
    'title',
    'start_at',
    'end_at',
    'user_id',
    'room_id',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  public function room()
  {
    return $this->belongsTo(Room::class, 'room_id', 'id');
  }

  public function getViewModel(): array
  {
    return [
      'id'      => $this->id,
      'url'     => $this->url,
      'type'    => $this->type,
      'title'   => $this->title,
      'startAt' => $this->start_at,
      'endAt'   => $this->end_at,
      'userId'  => $this->user_id,
      'roomId'  => $this->room_id,
    ];
  }
}
