<?php

namespace App\Http\Requests\Ketua;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateSantriRequest extends FormRequest
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
        /** @var SantriProfile $santri */
        $santri = $this->route('santri');

        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class, 'username')->ignore($santri->user_id),
                Rule::unique(SantriProfile::class, 'nis')->ignore($santri->id),
            ],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($santri->user_id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => ['nullable', 'date'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'school_level' => ['nullable', Rule::enum(SchoolLevel::class)],
            'track' => ['required', Rule::enum(SantriTrack::class)],
            'status' => ['required', Rule::enum(SantriStatus::class)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
