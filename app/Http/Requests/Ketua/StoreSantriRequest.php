<?php

namespace App\Http\Requests\Ketua;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreSantriRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', 'unique:'.User::class.',username', 'unique:'.SantriProfile::class.',nis'],
            'email' => ['nullable', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(SantriStatus::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
