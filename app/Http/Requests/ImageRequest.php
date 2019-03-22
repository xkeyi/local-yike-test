<?php

namespace App\Http\Requests;

class ImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|string|in:avatar',
            'image' => 'required|mimes:jpeg,bmp,png,gif',
        ];
    }
}
