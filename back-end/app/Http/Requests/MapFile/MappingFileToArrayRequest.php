<?php

namespace App\Http\Requests\MapFile;

use Illuminate\Foundation\Http\FormRequest;

class MappingFileToArrayRequest extends FormRequest
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
            'file' => ['required', 'mimes:xls,xlsx,csv'],
            'skipTop' => ['required', 'integer']
        ];
    }

    public function messages()
    {
        return [
            'file.required' => ':attribute is required',
            'skipTop.required' => ':attribute is required',
            'skipTop.integer' => ':attribute must be integer',
        ];
    }

    public function attributes()
    {
        return [
            'file' => 'Archivo',
            'skipTop' => 'Omitir al inicio'
        ];
    }
}
