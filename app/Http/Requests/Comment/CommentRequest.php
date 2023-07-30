<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Traits\ApiResponser;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommentRequest extends FormRequest
{
    use ApiResponser;
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
            'image_store_id' => 'required',
            'comment' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'image_store_id.required' => 'Mohon masukan id image_store',
            'comment.required' => 'Mohon masukan komentar kamu'

        ];
    }

    public function withValidator($validator)
    {
        $validator->addExtension('foobar', function ($attribute, $value, $parameters, $validator) {
            //
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        if ($validator->errors()->count() > 0) {
            throw new HttpResponseException($this->unProsesEntityResponse($validator->errors()->first(), $validator->errors()));
        }
    }
}
