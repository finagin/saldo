<?php

namespace App\Http\Requests\Saldo;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'dropzone-file' => [
                'required',
                'size:2',
            ],
            'dropzone-file.*' => [
                'mimes:xls,xlsx,csv',
            ],
        ];
    }
}
