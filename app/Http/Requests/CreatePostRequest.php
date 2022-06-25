<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'string|required',
            'description' => 'string|required',
            'image' => 'file|required|mimes:jpeg,jpg,png'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => $validator->errors()
        ], 401));
    }
}
