<?php

namespace App\Http\Requests\Ketua;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHalaqahMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-master') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santri_profiles,id'],
            'started_at' => ['required', 'date'],
        ];
    }
}
