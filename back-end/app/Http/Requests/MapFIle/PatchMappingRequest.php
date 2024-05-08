<?php

namespace App\Http\Requests\MapFile;

use Illuminate\Foundation\Http\FormRequest;

class PatchMappingRequest extends FormRequest
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
            'description'   => ['required'],
            'map'           => ['required'],
            'dateFormat'    => ['required'],
            'separator'     => ['required'],
            'skipBottom'    => ['required'],
            'skipTop'       => ['required'],
        ];
    }
}
