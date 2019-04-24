<?php

namespace App\Http\Requests;

class ThreadRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'title' => 'required|min:6|user_unique_content:threads,title',
                    'type' => 'required|in:markdown,html',
                    'content.body' => 'required_if:type,html',
                    'content.markdown' => 'required_if:type,markdown',
                    'is_draft' => 'boolean',
                    'node_id' => 'required|exists:nodes,id',
                ];
                break;

            case 'PUT':
            case 'PATCH':
                return [
                    'title' => 'required|min:6|user_unique_content:threads,title,'.$this->route()->parameters['thread']['id'],
                    'type' => 'in:markdown,html',
                    'content.body' => 'required_if:type,html',
                    'content.markdown' => 'required_if:type,markdown',
                    'is_draft' => 'boolean',
                    'node_id' => 'required|exists:nodes,id',
                ];
                break;

            default:
                return [];
                break;
        }
    }

    public function messages()
    {
        return [
            'title.user_unique_content' => '相同的标题已被发布过了！',
        ];
    }
}
