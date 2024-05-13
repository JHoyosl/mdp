<?php

namespace App\Http\Requests\TxType;

use App\Models\LocalTxType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TxTypeUpdateLocalRequest extends FormRequest
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
            'tx' => ['required'],
            'reference' => ['required', 'string'],
            'sign' => ['required', 'string'],
            'type' => ['required', Rule::in([LocalTxType::SIMPLE_TYPE, LocalTxType::COMPUESTO_TYPE])],
        ];
    }
}
