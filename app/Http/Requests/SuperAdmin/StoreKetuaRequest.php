<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreKetuaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'place_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:30'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'place_name.required' => 'Nama tempat wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'phone.required' => 'Telepon wajib diisi.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'place_name' => 'nama tempat',
            'address' => 'alamat',
            'phone' => 'telepon',
            'username' => 'username',
            'email' => 'email',
            'password' => 'kata sandi',
        ];
    }
}
