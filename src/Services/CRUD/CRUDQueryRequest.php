<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Foundation\Http\FormRequest;

class CRUDQueryRequet extends FormRequest {

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
            'filters' => 'required|string',
            '' => 'string|min:6|confirmed|required',
        ];
    }


}
