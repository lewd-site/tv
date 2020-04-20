<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $message
 * @property int $user_id
 * @property int $room_id
 * @property Carbon $created_at
 */
class ChatMessage extends Model
{
  const UPDATED_AT = null;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'message',
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
}
