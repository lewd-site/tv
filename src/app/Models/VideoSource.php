<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $url
 * @property string $title
 * @property bool $default
 * @property int $video_id
 * @property Video $video
 */
class VideoSource extends Model
{
  public $timestamps = false;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'url',
    'title',
    'default',
    'video_id',
  ];

  public function video()
  {
    return $this->belongsTo(Video::class, 'video_id', 'id');
  }

  public function getViewModel(): array
  {
    return [
      'id'      => $this->id,
      'url'     => $this->url,
      'title'   => $this->title,
      'default' => $this->default,
      'videoId' => $this->video_id,
    ];
  }
}
