<?php

namespace App\Http\Requests\Ops;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateSetoranRequest extends StoreSetoranRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['santri_id']);
        $rules['correction_note'] = ['required', 'string', 'max:500'];

        return $rules;
    }
}
