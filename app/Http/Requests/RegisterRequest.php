<?php

namespace App\Http\Requests;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|min:5|username|keep_word|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            // 'ticket' => 'required|ticket:register',
        ];
    }

    public function messages()
    {
        return [
            'username.keep_word' => '用户名 :input 不可用。',
        ];
    }
}
