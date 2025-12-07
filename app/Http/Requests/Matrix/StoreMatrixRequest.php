<?php

namespace App\Http\Requests\Matrix;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatrixRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'size'  => ['required', 'integer', 'min:1', 'max:200'],
            'grid'  => ['required', 'string'], // The raw "1 2 3\n4 5 6"
        ];
    }
}
