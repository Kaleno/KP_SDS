<?php

namespace App\Http\Requests\Ketua;

use App\Enums\EducationLevel;
use App\Models\User;
use App\Support\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUstazRequest extends FormRequest
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
        /** @var User $ustaz */
        $ustaz = $this->route('ustaz');

        return [
            'teaching_role' => ['required', 'in:'.Role::KetuaPengajar.','.Role::Pengajar],
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', Rule::unique(User::class, 'nip')->ignore($ustaz->id)],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique(User::class)->ignore($ustaz)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($ustaz)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'is_active' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
