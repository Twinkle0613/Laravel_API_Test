<?php

namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;
use Request;
use App\Http\Requests\Api\FormRequest;

class ImportUserRequest extends FormRequest
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
            'file'       => 'required',
            'extension'  => 'required|in:txt,csv,xlsx'
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'File is required!',
            'extension.required' => 'File Type is wrong!',
            'extension.in' => 'File Type is wrong!',
        ];
    }

    public function all($keys = null) 
    {
        $data = parent::all($keys);
        $file = Request::file('file');
        $data['extension'] = $file?strtolower($file->getClientOriginalExtension()):null;
        return $data;
    }
}
