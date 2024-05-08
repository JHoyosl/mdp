<?php

namespace App\Http\Requests\MapFile;

use App\Models\MapFile;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MappingUploadRequest extends FormRequest
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
            'type'          => ['required', Rule::in([MapFile::TYPE_EXTERNAL, MapFile::TYPE_INTERNAL])],
            'bankId'        => ['required:type,==,' . MapFile::TYPE_INTERNAL],
            'description'   => ['required'],
            'dateFormat'    => ['required'],
            'separator'     => ['required'],
            'skipBottom'    => ['required'],
            'skipTop'       => ['required'],
            'map'           => ['required'],
            'base'          => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'type.required'         => ':attribute is required',
            'bankId.required'       => ':attribute is required',
            'descriptioin.required' => ':attribute is required',
            'dateFormat.required'   => ':attribute is required',
            'separator.required'    => ':attribute is required',
            'skipTop.required'      => ':attribute is required',
            'skipBottom.required'   => ':attribute is required',
            'map.required'          => ':attribute is required',
            'base.required'         => ':attribute is required',
            'file.required'         => ':attribute is required',

        ];
    }

    public function attributes()
    {
        return [
            'type'          => 'tipo',
            'bankId'        => 'BancoId',
            'description'   => 'Descripción',
            'dateFormat'    => 'Formato Fecha',
            'separator'     => 'Separador',
            'skipTop'       => 'Omitir al inicio',
            'skipBottom'    => 'Omitir al final',
            'map'           => 'Mappeo',
            'base'          => 'base',
            'file'          => 'Archivo',
        ];
    }
}
