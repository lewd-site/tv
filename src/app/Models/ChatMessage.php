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
 * @property User $user
 * @property Room $room
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

  public function getViewModel(): array
  {
    return [
      'id'         => $this->id,
      'message'    => $this->message,
      'userId'     => $this->user_id,
      'userName'   => $this->user->name,
      'userUrl'    => route('users.show', ['id' => $this->user->id]),
      'userAvatar' => 'https://www.gravatar.com/avatar/' . md5(strtolower($this->user->email)) . '.jpg?s=24&d=mp',
      'roomId'     => $this->room_id,
      'roomName'   => $this->room->name,
      'createdAt'  => $this->created_at->toIso8601String(),
    ];
  }
}
