<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
            //
            'name' => 'required|Alpha|between:2,100',
            'email' => 'required|string|email:filter|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ];
    }

    public function failedValidation(Validator $v)
    {
        throw new HttpResponseException(response()->json([
            'status'=> false,
            'message'=> 'Validation error',
            'data'=> $v->errors()
        ]));
    }

    public function messages()
    {
        return [
            'name.required' => 'A name is required',
            'email.unique' => 'invalid email',
        ];
    }
}
