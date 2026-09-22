<?php

namespace App\Http\Requests\Ops;

use App\Support\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSppPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->hasRole(Role::Ketua) || $user->hasRole(Role::KetuaPengajar));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santri_profiles,id'],
            'from_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'from_month' => ['required', 'integer', 'min:1', 'max:12'],
            'to_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'to_month' => ['required', 'integer', 'min:1', 'max:12'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $from = ((int) $this->input('from_year') * 100) + (int) $this->input('from_month');
            $to = ((int) $this->input('to_year') * 100) + (int) $this->input('to_month');

            if ($from > $to) {
                $validator->errors()->add('to_month', 'Bulan akhir harus sama atau setelah bulan awal.');
            }
        });
    }
}
