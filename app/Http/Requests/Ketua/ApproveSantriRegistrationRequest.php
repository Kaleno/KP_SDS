<?php

namespace App\Http\Requests\Ketua;

use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ApproveSantriRegistrationRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:'.User::class],
            'nis' => ['required', 'string', 'max:30', 'unique:'.SantriProfile::class.',nis'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
