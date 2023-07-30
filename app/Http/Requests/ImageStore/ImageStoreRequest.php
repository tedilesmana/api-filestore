<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Traits\ApiResponser;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImageStoreRequest extends FormRequest
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
            'name' => 'required',
            'description' => 'required',
            'image_url' => 'required',
            'filename' => 'required',
            'extention' => 'required',
            'size' => 'required',
            'directory' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Mohon masukan nama gambar',
            'description.required' => 'Mohon masukan deskripsi',
            'image_url.required' => 'Mohon masukan image url',
            'filename.required' => 'Mohon masukan nama file',
            'extention.required' => 'Mohon masukan extention file',
            'size.required' => 'Mohon masukan size file',
            'directory.required' => 'Mohon masukan directory file',

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
