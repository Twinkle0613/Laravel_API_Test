<?php

namespace App\Http\Requests;
use App\Http\Requests\Api\FormRequest;

class ShowUserRequest extends FormRequest
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
            'id' => 'required|exists:users,id',
        ];
    }

    public function all($keys = null) 
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('user');
        return $data;
    }

}
