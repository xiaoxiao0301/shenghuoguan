<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required',
            'folder' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => '文件是必须的',
            'folder.required' => '目录是必须的'
        ];
    }
}
