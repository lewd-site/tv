<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $url
 * @property string $name
 * @property int $user_id
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
}
