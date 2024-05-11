<?php

namespace App\Http\Requests\TxType;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TxTypeStoreExternalRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'tx' => 'required|string',
            'bankId' => 'required',
            'reference' => 'required|string',
            'type' => ['required', 'string', Rule::in(['SIMPLE', 'COMPUESTO'])],
            'sign' => 'required|string',
        ];
    }
}
