<?php

namespace App\Http\Requests\Ketua;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MutateHalaqahMemberRequest extends FormRequest
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
            'target_halaqah_id' => ['required', 'exists:halaqah,id'],
            'moved_at' => ['required', 'date'],
            'mutation_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
