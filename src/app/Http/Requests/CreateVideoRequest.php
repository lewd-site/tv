<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVideoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return auth()->id() === $this->room->user_id;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'url'   => 'required|max:2048',
      'start' => 'sometimes|required|regex:/^(?:(?:\d+:)?[0-5]?\d:)?[0-5]?\d$/|max:10',
      'end'   => 'sometimes|required|regex:/^(?:(?:\d+:)?[0-5]?\d:)?[0-5]?\d$/|max:10',
    ];
  }
}
