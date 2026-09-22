<?php

namespace App\Http\Requests\Ketua;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSppAmountRequest extends FormRequest
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
            'spp_amount' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'spp_due_day' => ['required', 'integer', 'min:1', 'max:28'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'spp_amount.min' => 'Biaya SPP minimal Rp 1.000.',
            'spp_amount.max' => 'Biaya SPP terlalu besar.',
            'spp_due_day.min' => 'Tanggal jatuh tempo minimal tanggal 1.',
            'spp_due_day.max' => 'Tanggal jatuh tempo maksimal tanggal 28.',
        ];
    }
}
